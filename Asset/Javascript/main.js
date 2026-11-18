// Core scripts are not loaded on public pages
if (typeof KB !== 'undefined') {
    KB.on('dom.ready', function () {
        function goToLink(selector) {
            if (!KB.modal.isOpen()) {
                let element = KB.find(selector);

                if (element !== null) {
                    window.location = element.attr('href');
                }
            }
        }

        KB.onKey('v+w', function () {
            goToLink('a.view-wiki');
        });
    });

    // wiki: suggestions in the markdown editor, next to # (tasks) and @ (users)
    var wikiGetComponent = KB.getComponent;
    KB.getComponent = function (name, containerElement, options) {
        var meta = document.querySelector('meta[name="kanboard-wiki-suggest-url"]');

        if (name === 'suggest-menu' && meta && options && options.triggers && !options.triggers['wiki:']) {
            options.triggers['wiki:'] = meta.content;
        }

        return wikiGetComponent.apply(this, arguments);
    };
}
