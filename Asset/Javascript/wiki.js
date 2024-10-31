jQuery(document).ready(function () {
    //--------------------------------------
    // hide the KB filters toolbar
    //--------------------------------------
    $('.input-addon-field').hide();
    $('.input-addon-item').hide();

    //--------------------------------------
    // override titles for links from their container buttons
    //--------------------------------------
    $("#wikitree").find(".action a").each(function () {
        $(this).attr('title', $(this).parent().attr('title'));
    });
    $("#wikilist").find(".action a").each(function () {
        $(this).attr('title', $(this).parent().attr('title'));
    });

    //--------------------------------------
    // handle collapse/expand of wikitree branches
    //--------------------------------------
    if($("#wikitree").length == 1) {
        function expandAllWikipagesBranches() {
            const buttons = $("#wikitree").find(".branch");
            buttons.each(function () {
                const button = $(this).find("a i")[0];
                const branch = $(this).parent().find("ul")[0];
                $(button).removeClass('fa-plus-square-o');
                $(button).addClass('fa-minus-square-o');
                $(branch).show();
            });
        }

        function collapseAllWikipagesBranches() {
            const buttons = $("#wikitree").find(".branch");
            buttons.each(function () {
                const button = $(this).find("a i")[0];
                const branch = $(this).parent().find("ul")[0];
                $(button).removeClass('fa-minus-square-o');
                $(button).addClass('fa-plus-square-o');
                $(branch).hide();
            });
        }

        function gotoSelectedWikipageBranch() {
            function expandParentWikipage(el) {
                const parentUl = el.parent();
                const parentId = parentUl.attr("data-parent-id");
                if (parentId == 0) return; // end recursion

                const parentWikipage = parentUl.parent();
                const button = parentWikipage.find(".branch a i")[0];
                $(button).removeClass('fa-plus-square-o');
                $(button).addClass('fa-minus-square-o');
                parentUl.show();

                expandParentWikipage(parentWikipage);
            }

            const selected = $("#wikitree").find(".wikipage.active");
            expandParentWikipage(selected);
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

        $('.branch').click(function () {
            const button = $(this).find("a i")[0];
            const branch = $(this).parent().find("ul")[0];
            console.log(branch);
            if ($(button).hasClass('fa-minus-square-o')) {
                $(button).removeClass('fa-minus-square-o');
                $(button).addClass('fa-plus-square-o');
                $(branch).hide();
                console.log('hide')
                return;
            }
            if ($(button).hasClass('fa-plus-square-o')) {
                $(button).removeClass('fa-plus-square-o');
                $(button).addClass('fa-minus-square-o');
                $(branch).show();
                console.log('show')
                return;
            }
        });

        collapseAllWikipagesBranches();
        gotoSelectedWikipageBranch();
    }

    //--------------------------------------
    // page reorder and nesting support using jquery sorting plugin
    //--------------------------------------
    if($("#wikitree").data("reorder-url")){
        $('#wikitree').sortable({
            nested: true,
            onDrop: function ($item, container, _super) {
            //   console.log("onDrop", $item, container, _super)
            container.el.removeClass("active");
            var srcProperties = {
                ...$item[0].dataset
            }
            var containerProperties = {...container.el[0].dataset}

            let request = {
                "src_wiki_id": srcProperties["pageId"],
                "index": $item.index(),
                "parent_id": containerProperties["parentId"]
            }

            // console.log("request", request)


            $.ajax({
                    cache: false,
                    url: $("#wikitree").data("reorder-url"),
                    contentType: "application/json",
                    type: "POST",
                    processData: false,
                    data: JSON.stringify(request),
                    success: function(data) {
                        // self.refresh(data);
                        // self.savingInProgress = false;
                    },
                    error: function() {
                        // self.app.hideLoadingIcon();
                        // self.savingInProgress = false;
                    },
                    statusCode: {
                        403: function(data) {
                            window.alert(data.responseJSON.message);
                            document.location.reload(true);
                        }
                    }
                });
            _super($item, container);
            },
        })
    }
});
