/*
 * Wiki editions: shows what changed between an edition and the one before it.
 * Markdown view: git-style line diff (unified or side by side) with changed words marked.
 * Rendered view: the server-rendered pages compared block by block.
 * User text is only ever inserted with textContent; the rendered view clones the server's own markup.
 */
(function () {
    'use strict';

    var CONTEXT = 3;
    var MAX_EDIT_DISTANCE = 2000;
    var STORAGE_KEY = 'wiki-diff-options';
    var options = { view: 'markdown', layout: 'unified' };
    var texts = {};
    var labels = { lines: 'Show %d unchanged lines', blocks: 'Show %d unchanged blocks', same: 'The content did not change.' };

    // --- Myers diff on arrays: ops are ['=', oldIndex, newIndex], ['-', oldIndex, null], ['+', null, newIndex]
    function diff(a, b, eq) {
        eq = eq || function (x, y) { return x === y; };
        var n = a.length, m = b.length, prefix = 0, suffix = 0, i, ops = [];
        while (prefix < n && prefix < m && eq(a[prefix], b[prefix])) { prefix++; }
        while (suffix < n - prefix && suffix < m - prefix && eq(a[n - 1 - suffix], b[m - 1 - suffix])) { suffix++; }
        for (i = 0; i < prefix; i++) { ops.push(['=', i, i]); }
        myers(a.slice(prefix, n - suffix), b.slice(prefix, m - suffix), eq).forEach(function (op) {
            ops.push([op[0], op[1] === null ? null : op[1] + prefix, op[2] === null ? null : op[2] + prefix]);
        });
        for (i = 0; i < suffix; i++) { ops.push(['=', n - suffix + i, m - suffix + i]); }
        return ops;
    }

    function myers(a, b, eq) {
        var n = a.length, m = b.length, v = { 1: 0 }, trace = [], d, k, x, y;
        for (d = 0; d <= n + m; d++) {
            if (d > MAX_EDIT_DISTANCE) { return replaceAll(n, m); }
            trace.push(Object.assign({}, v));
            for (k = -d; k <= d; k += 2) {
                x = (k === -d || (k !== d && v[k - 1] < v[k + 1])) ? v[k + 1] : v[k - 1] + 1;
                y = x - k;
                while (x < n && y < m && eq(a[x], b[y])) { x++; y++; }
                v[k] = x;
                if (x >= n && y >= m) { return backtrack(trace, n, m); }
            }
        }
        return replaceAll(n, m);
    }

    function backtrack(trace, n, m) {
        var x = n, y = m, ops = [], d, v, k, previousK, previousX, previousY;
        for (d = trace.length - 1; d >= 0; d--) {
            v = trace[d];
            k = x - y;
            previousK = (k === -d || (k !== d && v[k - 1] < v[k + 1])) ? k + 1 : k - 1;
            previousX = v[previousK];
            previousY = previousX - previousK;
            while (x > previousX && y > previousY) { ops.push(['=', x - 1, y - 1]); x--; y--; }
            if (d > 0) { ops.push(x === previousX ? ['+', null, y - 1] : ['-', x - 1, null]); }
            x = previousX;
            y = previousY;
        }
        return ops.reverse();
    }

    function replaceAll(n, m) {
        var ops = [], i;
        for (i = 0; i < n; i++) { ops.push(['-', i, null]); }
        for (i = 0; i < m; i++) { ops.push(['+', null, i]); }
        return ops;
    }

    // --- DOM helpers
    function el(tag, className, text) {
        var node = document.createElement(tag);
        if (className) { node.className = className; }
        if (text !== undefined) { node.textContent = text; }
        return node;
    }

    function words(line) {
        return line.match(/\s+|[\p{L}\p{N}_]+|[^\s\p{L}\p{N}_]/gu) || [];
    }

    // fills a code cell, marking the words that differ from the paired line
    function fillCode(cell, line, other, side) {
        if (other === null) { cell.textContent = line; return; }
        var a = words(side === '-' ? line : other), b = words(side === '-' ? other : line);
        var mark = null;
        diff(a, b).forEach(function (op) {
            var token = side === '-' ? a[op[1]] : b[op[2]];
            if (op[0] === '=') {
                mark = null;
                cell.appendChild(document.createTextNode(token));
            } else if (op[0] === side) {
                if (!mark) { mark = cell.appendChild(el(side === '-' ? 'del' : 'ins')); }
                mark.textContent += token;
            }
        });
    }

    // groups ops into rows: equal lines, and change blocks of paired deleted/inserted lines
    function lineRows(oldLines, newLines) {
        var rows = [], block = null;
        diff(oldLines, newLines).forEach(function (op) {
            if (op[0] === '=') {
                block = null;
                rows.push({ type: '=', oldNo: op[1] + 1, newNo: op[2] + 1, text: oldLines[op[1]] });
                return;
            }
            if (!block) { block = { type: 'change', removed: [], added: [] }; rows.push(block); }
            if (op[0] === '-') { block.removed.push({ no: op[1] + 1, text: oldLines[op[1]] }); }
            else { block.added.push({ no: op[2] + 1, text: newLines[op[2]] }); }
        });
        return rows;
    }

    function cell(tag, className, text) {
        return el(tag || 'td', className, text === undefined ? '' : String(text));
    }

    function codeRow(cells) {
        var tr = el('tr');
        cells.forEach(function (c) { tr.appendChild(c); });
        return tr;
    }

    function unifiedLine(type, oldNo, newNo, text, pair) {
        var code = cell('td', 'wiki-diff-code');
        fillCode(code, text, pair, type);
        var tr = codeRow([cell('td', 'wiki-diff-ln', oldNo), cell('td', 'wiki-diff-ln', newNo), cell('td', 'wiki-diff-sign', type === '=' ? ' ' : (type === '-' ? '−' : '+')), code]);
        tr.className = type === '=' ? 'wiki-diff-eq' : (type === '-' ? 'wiki-diff-del' : 'wiki-diff-ins');
        return tr;
    }

    function splitHalf(entry, pair, type) {
        var ln = cell('td', 'wiki-diff-ln', entry ? entry.no : ''), code = cell('td', 'wiki-diff-code');
        if (entry) {
            fillCode(code, entry.text, pair ? pair.text : null, type);
            ln.className += type === '-' ? ' wiki-diff-del' : ' wiki-diff-ins';
            code.className += type === '-' ? ' wiki-diff-del' : ' wiki-diff-ins';
        } else {
            code.className += ' wiki-diff-empty';
        }
        return [ln, code];
    }

    function markdownTable(oldText, newText) {
        var table = el('table', 'wiki-diff-table wiki-diff-' + options.layout), tbody = el('tbody'), items = [];
        var colgroup = el('colgroup');
        (options.layout === 'split' ? ['ln', 'code', 'ln', 'code'] : ['ln', 'ln', 'sign', 'code']).forEach(function (name) {
            colgroup.appendChild(el('col', 'wiki-diff-col-' + name));
        });
        table.appendChild(colgroup);
        table.appendChild(tbody);
        lineRows(oldText.split('\n'), newText.split('\n')).forEach(function (row) {
            if (row.type === '=') {
                var tr = options.layout === 'split'
                    ? codeRow([cell('td', 'wiki-diff-ln', row.oldNo), cell('td', 'wiki-diff-code', row.text), cell('td', 'wiki-diff-ln', row.newNo), cell('td', 'wiki-diff-code', row.text)])
                    : unifiedLine('=', row.oldNo, row.newNo, row.text, null);
                tr.className = 'wiki-diff-eq';
                items.push({ equal: true, node: tr });
                return;
            }
            var i, count = Math.max(row.removed.length, row.added.length);
            if (options.layout === 'split') {
                for (i = 0; i < count; i++) {
                    items.push({ equal: false, node: codeRow(splitHalf(row.removed[i], row.added[i], '-').concat(splitHalf(row.added[i], row.removed[i], '+'))) });
                }
            } else {
                row.removed.forEach(function (line, j) { items.push({ equal: false, node: unifiedLine('-', line.no, '', line.text, row.added[j] ? row.added[j].text : null) }); });
                row.added.forEach(function (line, j) { items.push({ equal: false, node: unifiedLine('+', '', line.no, line.text, row.removed[j] ? row.removed[j].text : null) }); });
            }
        });
        appendFolded(tbody, items, CONTEXT, function (count) {
            var td = el('td', 'wiki-diff-fold');
            td.colSpan = 4;
            var tr = el('tr');
            tr.appendChild(td);
            return { row: tr, target: td };
        });
        return table;
    }

    function renderedDiff(oldArticle, newArticle) {
        var out = el('div', 'markdown wiki-diff-rendered'), items = [];
        var a = Array.prototype.slice.call(oldArticle.children), b = Array.prototype.slice.call(newArticle.children);
        diff(a, b, function (x, y) { return x.outerHTML === y.outerHTML; }).forEach(function (op) {
            var node = (op[0] === '-' ? a[op[1]] : b[op[2]]).cloneNode(true);
            if (op[0] !== '=') {
                var wrap = el('div', op[0] === '-' ? 'wiki-diff-block-del' : 'wiki-diff-block-ins');
                wrap.appendChild(node);
                node = wrap;
            }
            items.push({ equal: op[0] === '=', node: node });
        });
        appendFolded(out, items, 1, function () {
            var div = el('div', 'wiki-diff-fold');
            return { row: div, target: div };
        });
        return out;
    }

    // long runs of unchanged items are hidden behind a "show" button, keeping some context around each change
    function appendFolded(parent, items, context, makeFold) {
        var i = 0;
        while (i < items.length) {
            if (!items[i].equal) { parent.appendChild(items[i++].node); continue; }
            var start = i;
            while (i < items.length && items[i].equal) { i++; }
            var keepBefore = start === 0 ? 0 : context, keepAfter = i === items.length ? 0 : context;
            if (i - start < keepBefore + keepAfter + 4) {
                items.slice(start, i).forEach(function (item) { parent.appendChild(item.node); });
                continue;
            }
            var hidden = items.slice(start + keepBefore, i - keepAfter);
            items.slice(start, start + keepBefore).forEach(function (item) { parent.appendChild(item.node); });
            if (hidden.length > 1) {
                var fold = makeFold(hidden.length), button = el('button', 'btn');
                button.type = 'button';
                button.textContent = (parent.tagName === 'TBODY' ? labels.lines : labels.blocks).replace('%d', hidden.length);
                fold.target.appendChild(button);
                parent.appendChild(fold.row);
                hidden.forEach(function (item) { item.node.hidden = true; parent.appendChild(item.node); });
                button.addEventListener('click', (function (nodes, row) {
                    return function () {
                        nodes.forEach(function (item) { item.node.hidden = false; });
                        row.parentNode.removeChild(row);
                    };
                })(hidden, fold.row));
            } else {
                hidden.forEach(function (item) { parent.appendChild(item.node); });
            }
            items.slice(i - keepAfter, i).forEach(function (item) { parent.appendChild(item.node); });
        }
    }

    function article(edition) {
        return document.querySelector('.wikiedition[data-edition="' + edition + '"] article.markdown');
    }

    function render(container) {
        var oldId = container.getAttribute('data-old'), newId = container.getAttribute('data-new');
        var own = article(newId), base = article(oldId);
        if (texts[oldId] === undefined || texts[newId] === undefined || !own || !base) { return; }
        container.textContent = '';
        if (texts[oldId] === texts[newId]) {
            container.appendChild(el('p', 'wiki-diff-none', labels.same));
        } else {
            container.appendChild(options.view === 'rendered' ? renderedDiff(base, own) : markdownTable(texts[oldId], texts[newId]));
        }
        container.setAttribute('data-rendered', '1');
        // inside an edition, the diff replaces that edition's full content
        if (container.closest('.wikiedition') === own.closest('.wikiedition')) { own.hidden = true; }
    }

    function renderOpen() {
        document.querySelectorAll('.wiki-diff').forEach(function (container) {
            var details = container.closest('details');
            if (!details || details.open) { render(container); }
        });
    }

    function syncButtons() {
        document.querySelectorAll('[data-wiki-diff-view]').forEach(function (b) { b.setAttribute('aria-pressed', String(b.getAttribute('data-wiki-diff-view') === options.view)); });
        document.querySelectorAll('[data-wiki-diff-layout]').forEach(function (b) {
            b.setAttribute('aria-pressed', String(b.getAttribute('data-wiki-diff-layout') === options.layout));
            b.disabled = options.view === 'rendered';
        });
    }

    function init() {
        var data = document.getElementById('wiki-edition-texts');
        if (!data) { return; }
        try { texts = JSON.parse(data.textContent) || {}; } catch (e) { return; }
        try { Object.assign(options, JSON.parse(window.localStorage.getItem(STORAGE_KEY)) || {}); } catch (e) { /* storage unavailable */ }

        var toolbar = document.querySelector('.wiki-diff-options');
        if (toolbar) {
            ['lines', 'blocks', 'same'].forEach(function (key) { labels[key] = toolbar.getAttribute('data-label-' + key) || labels[key]; });
            toolbar.hidden = false;
            toolbar.addEventListener('click', function (event) {
                var button = event.target.closest('button');
                if (!button) { return; }
                if (button.hasAttribute('data-wiki-diff-view')) { options.view = button.getAttribute('data-wiki-diff-view'); }
                if (button.hasAttribute('data-wiki-diff-layout')) { options.layout = button.getAttribute('data-wiki-diff-layout'); }
                try { window.localStorage.setItem(STORAGE_KEY, JSON.stringify(options)); } catch (e) { /* storage unavailable */ }
                syncButtons();
                document.querySelectorAll('.wiki-diff[data-rendered]').forEach(render);
            });
        }
        syncButtons();

        var compare = document.querySelector('.wiki-diff-compare');
        if (compare) {
            compare.hidden = false;
            compare.addEventListener('submit', function (event) {
                event.preventDefault();
                var from = compare.querySelector('[data-wiki-diff-from]').value, to = compare.querySelector('[data-wiki-diff-to]').value;
                var container = compare.querySelector('.wiki-diff');
                container.setAttribute('data-old', Math.min(from, to));
                container.setAttribute('data-new', Math.max(from, to));
                container.hidden = false;
                render(container);
            });
        }

        var target = /^#edition-\d+$/.test(window.location.hash) ? document.querySelector(window.location.hash + ' details') : null;
        if (target) { target.open = true; }

        document.querySelectorAll('.wiki-diff').forEach(function (container) {
            var details = container.closest('details');
            if (details) { details.addEventListener('toggle', function () { if (details.open) { render(container); } }); }
        });
        renderOpen();
    }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
