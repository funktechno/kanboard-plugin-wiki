jQuery(document).ready(function () {
    //--------------------------------------
    // hide the KB filters toolbar
    //--------------------------------------
    $('.input-addon-field').hide();
    $('.input-addon-item').hide();

    //--------------------------------------
    // handle collapse/expand of wikitree branches
    //--------------------------------------
    $('.branch').click(function() {
        const button = $(this).find("a i")[0];
        const branch = $(this).parent().find("ul")[0];
        if ($(button).hasClass( 'fa fa-minus-square' )) {
            $(button).removeClass( 'fa fa-minus-square' );
            $(button).addClass( 'fa fa-plus-square' );
            $(branch).hide();
            return;
        }
        if ($(button).hasClass( 'fa fa-plus-square' )) {
            $(button).removeClass( 'fa fa-plus-square' );
            $(button).addClass( 'fa fa-minus-square' );
            $(branch).show();
            return;
        }
    });

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
