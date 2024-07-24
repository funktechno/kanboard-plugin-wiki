jQuery(document).ready(function () {
    /*
       page reorder and nesting support using jquery sorting plugin
    */
    if($("#columns").data("reorder-url")){
        jQuery('#columns').sortable({
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
                    url: $("#columns").data("reorder-url"),
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
