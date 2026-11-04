jQuery(document).ready(function () {
    //--------------------------------------
    // device detection
    //--------------------------------------
    let isMobile = false;
    if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
        || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw-(n|u)|c55\/|capi|ccwa|cdm-|cell|chtm|cldc|cmd-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc-s|devi|dica|dmob|do(c|p)o|ds(12|-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(-|_)|g1 u|g560|gene|gf-5|g-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd-(m|p|t)|hei-|hi(pt|ta)|hp( i|ip)|hs-c|ht(c(-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i-(20|go|ma)|i230|iac( |-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|-[a-w])|libw|lynx|m1-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|-([1-8]|c))|phil|pire|pl(ay|uc)|pn-2|po(ck|rt|se)|prox|psio|pt-g|qa-a|qc(07|12|21|32|60|-[2-7]|i-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h-|oo|p-)|sdk\/|se(c(-|0|1)|47|mc|nd|ri)|sgh-|shar|sie(-|m)|sk-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h-|v-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl-|tdg-|tel(i|m)|tim-|t-mo|to(pl|sh)|ts(70|m-|m3|m5)|tx-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas-|your|zeto|zte-/i.test(navigator.userAgent.substr(0,4))) {
        isMobile = true;
    }

    //--------------------------------------
    // obtain the base app dir from the hidden element
    //--------------------------------------
    let baseAppDir = "";
    const baseAppDirField = $("#_Wiki_BaseAppDir_");
    if (baseAppDirField.length === 1) {
        // console.log(baseAppDirField.html());
        baseAppDir = baseAppDirField.html();
        baseAppDirField.remove();
    }

    //--------------------------------------
    // hide the KB board selector (for Search Wiki Pages ONLY)
    //--------------------------------------
    if($("#wikisearch").length == 1) {
        $('.board-selector-container').remove();
    }
    //--------------------------------------
    // hide the KB filters toolbar
    //--------------------------------------
    $('.filter-box').remove();
    $('.filter-box-component').addClass( 'containerFloatClear' );

    //--------------------------------------
    // override titles for links from their container buttons
    //--------------------------------------
    $("#wikitree").find(".action a").each(function () {
        $(this).attr('title', $(this).parent().attr('title'));
    });
    $("#wikilist").find(".action a").each(function () {
        $(this).attr('title', $(this).parent().attr('title'));
    });
    $("#wikiedition").find(".action a").each(function () {
        $(this).attr('title', $(this).parent().attr('title'));
    });

    //--------------------------------------
    // handle collapse/expand of wikitree branches
    //--------------------------------------

    //--------------------------------------
    // obtain the base app dir from the hidden element
    //--------------------------------------
    let expandedWikiBranches = new Set();
    const wikiExpandedBranchesField = $("#_Wiki_ExpandedBranches_");
    if (wikiExpandedBranchesField.length === 1) {
        // console.log(wikiExpandedBranchesField.html());
        expandedWikiBranches = new Set( JSON.parse(wikiExpandedBranchesField.html()) );
        // console.log(expandedWikiBranches);
        wikiExpandedBranchesField.remove();
    }

    function saveWikiTreeState() {
        // console.log(expandedWikiBranches);
        // console.log(encodeURIComponent(JSON.stringify([...expandedWikiBranches] )));

        // don't cache ajax or content won't be fresh
        $.ajax({
            cache: false,
            type: "POST",
            url: baseAppDir
                + '?controller=WikiController&action=wikitree&plugin=wiki'
                + '&wiki_tree=' + encodeURIComponent(JSON.stringify([...expandedWikiBranches] )),
            success: function(response) {
                // const wiki_tree = JSON.parse(response);
                // console.log(wiki_tree);
            },
            error: function(xhr,textStatus,e) {
                alert('saveWikiTreeState');
                alert(e);
            }
        });
    }

    if($("#wikitree").length == 1) {
        function expandAllWikipagesBranches() {
            const buttons = $("#wikitree").find(".branch");
            buttons.each(function () {
                const button = $(this).find("a i")[0];
                const branch = $(this).parent().find("ul")[0];
                const wiki_id = parseInt($(branch).attr('data-parent-id'));
                $(button).removeClass('fa-plus-square-o');
                $(button).addClass('fa-minus-square-o');
                $(branch).removeClass('hideMe');
                expandedWikiBranches.add(wiki_id);
            });
            saveWikiTreeState();
        }

        function collapseAllWikipagesBranches() {
            const buttons = $("#wikitree").find(".branch");
            buttons.each(function () {
                const button = $(this).find("a i")[0];
                const branch = $(this).parent().find("ul")[0];
                const wiki_id = parseInt($(branch).attr('data-parent-id'));
                $(button).removeClass('fa-minus-square-o');
                $(button).addClass('fa-plus-square-o');
                $(branch).addClass('hideMe');
                expandedWikiBranches.delete(wiki_id);
            });
            saveWikiTreeState();
        }

        function restoreExpandedWikiBranches() {
            $(".wikipage").each(function() {
                const branch_id = parseInt($(this).attr('data-page-id'));
                const button = $(this).find(".branch a i")[0];
                const branch = $(this).find("ul")[0];
                if (expandedWikiBranches.has(branch_id)) {
                    $(button).removeClass('fa-plus-square-o');
                    $(button).addClass('fa-minus-square-o');
                    $(branch).removeClass('hideMe');
                }
            });
        }

        function gotoSelectedWikipageBranch() {
            function expandParentWikipage(el) {
                const parentUl = el.parent();
                const parentId = parentUl.attr("id");

                const parentWikipage = parentUl.parent();
                const button = parentWikipage.find(".branch a i")[0];
                const branch = parentWikipage.find("ul")[0];
                const wiki_id = parseInt($(branch).attr('data-parent-id'));
                $(button).removeClass('fa-plus-square-o');
                $(button).addClass('fa-minus-square-o');
                parentUl.removeClass('hideMe');
                expandedWikiBranches.add(wiki_id);

                if (parentId === "wikiroot") return; // end recursion
                expandParentWikipage(parentWikipage);
            }

            const selected = $("#wikiroot").find(".wikipage.selected");
            expandParentWikipage(selected);

            saveWikiTreeState();
        }

        $('.expandAll').click(function () {
            expandAllWikipagesBranches();
        });

        $('.collapseAll').click(function () {
            collapseAllWikipagesBranches();
        });

        $('.gotoSelected').click(function () {
            gotoSelectedWikipageBranch();
        });

        $('#wikitree .branch').click(function () {
            const button = $(this).find("a i")[0];
            const branch = $(this).parent().find("ul")[0];
            const wiki_id = parseInt($(branch).attr('data-parent-id'));

            if ($(button).hasClass('fa-minus-square-o')) {
                $(button).removeClass('fa-minus-square-o');
                $(button).addClass('fa-plus-square-o');
                $(branch).addClass('hideMe');
                expandedWikiBranches.delete(wiki_id);
                saveWikiTreeState();
                return;
            }
            if ($(button).hasClass('fa-plus-square-o')) {
                $(button).removeClass('fa-plus-square-o');
                $(button).addClass('fa-minus-square-o');
                $(branch).removeClass('hideMe');
                expandedWikiBranches.add(wiki_id);
                saveWikiTreeState();
                return;
            }
        });

        // restore expanded branches and then go to the selected branch
        restoreExpandedWikiBranches();
        if ($("#wikitree").length == 1 && $("#wikitree").data("selected-wiki-id") != 0) {
            gotoSelectedWikipageBranch();
        }
    }

    //--------------------------------------
    // page reorder and nesting support using jquery sorting plugin
    //--------------------------------------
    if($("#wikiroot").length == 1 && $("#wikitree").length == 1 && $("#wikitree").data("reorder-url")) {
        if (isMobile) {
            $(".sortable-handle").show();
        }

        $('#wikiroot').sortable({
            nested: true,
            itemSelector: ".wikipage",
            placeholder: '<li class="sortable-placeholder"></li>',
            placeholderClass: "sortable-placeholder",
            draggedClass: "sortable-dragged",
            distance: 10,
            handle: isMobile ? ".sortable-handle" : ".wikibranch",
            onDrop: function ($item, container, _super) {
                // console.log("onDrop", $item, container, _super)

                const itemProperties = { ...$item[0].dataset }
                const containerProperties = { ...container.el[0].dataset }
                // console.log(itemProperties)
                // console.log(containerProperties)

                const request = {
                    "src_wiki_id": itemProperties["pageId"],
                    "index": $item.index() + 1,
                    "parent_id": containerProperties["parentId"],
                }
                // console.log("request", request)

                $.ajax({
                    cache: false,
                    url: $("#wikitree").data("reorder-url"),
                    contentType: "application/json",
                    type: "POST",
                    processData: false,
                    data: JSON.stringify(request),
                    success: function(/*data*/) {
                        // alert(data);
                        location.reload();
                    },
                    error: function(xhr,textStatus,e) {
                        alert(xhr.responseText);
                        location.reload();
                    },
                    statusCode: {
                        403: function(data) {
                            alert(data.responseJSON.message);
                            location.reload();
                        }
                    }
                });
                _super($item, container);
            },
        });
    }
});
