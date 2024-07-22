jQuery(document).ready(function () {
    /*
    var dragSrcEl = null;

    //  TODO: nesting support? 
    //     * use sortable js
    //     * jquery ui sortable? 
    //     * auto scroll for long lists
    //     * raw javascript:https://www.cssscript.com/sort-nested-list/
    // attempt to do sortable with no new dependencies, need nested support 
    // https://stackoverflow.com/questions/3308672/sortable-nested-lists-with-jquery-ui-1-8-2
    

    // original example
    // https://web.dev/articles/drag-and-drop
    function handleDragStart(e) {
        // Target (this) element is the source node.
        dragSrcEl = this;

        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.outerHTML);

        this.classList.add('dragElem');
    }
    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault(); // Necessary. Allows us to drop.
        }
        this.classList.add('over');

        e.dataTransfer.dropEffect = 'move';  // See the section on the DataTransfer object.

        return false;
    }

    function handleDragEnter(e) {
        // this / e.target is the current hover target.
    }

    function handleDragLeave(e) {
        this.classList.remove('over');  // this / e.target is previous target element.
    }

    function handleDrop(e) {
        // this/e.target is current target element.

        if (e.stopPropagation) {
            e.stopPropagation(); // Stops some browsers from redirecting.
        }

        // Don't do anything if dropping the same column we're dragging.
        if (dragSrcEl != this) {
            
            let targetRoute;
            if(e.target.localName == "a"){
                targetRoute = e.target.href
            } else {
                targetRoute = e.target.querySelector("a").href
            }
            let targetParams = new URL(targetRoute)
            var targetProperties = {}
            for (const [key, value] of targetParams.searchParams.entries()) {
                targetProperties[key] = value
            }
            // console.log("targetProperties", targetProperties)
            
            var srcParams = new URL(dragSrcEl.querySelector("a").href)
            var srcProperties = {}

            for (const [key, value] of srcParams.searchParams.entries()) {
                srcProperties[key] = value
            }
            // console.log("srcProperties", srcProperties)

            let project_id = srcProperties["project_id"]

            // console.log("project_id", project_id)

            let request = {
                "src_wiki_id": srcProperties["wiki_id"],
                "target_wiki_id": targetProperties["wiki_id"]
            }

            console.log("request", request)

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

            // Set the source column's HTML to the HTML of the column we dropped on.
            //alert(this.outerHTML);
            //dragSrcEl.innerHTML = this.innerHTML;
            //this.innerHTML = e.dataTransfer.getData('text/html');
            this.parentNode.removeChild(dragSrcEl);
            var dropHTML = e.dataTransfer.getData('text/html');
            this.insertAdjacentHTML('beforebegin', dropHTML);
            var dropElem = this.previousSibling;
            addDnDHandlers(dropElem);

        }
        this.classList.remove('over');
        return false;
    }

    function handleDragEnd(e) {
        // this/e.target is the source node.
        this.classList.remove('over');

        // [].forEach.call(cols, function (col) {
        //   col.classList.remove('over');
        // });
    }

    function addDnDHandlers(elem) {
        elem.addEventListener('dragstart', handleDragStart, false);
        elem.addEventListener('dragenter', handleDragEnter, false)
        elem.addEventListener('dragover', handleDragOver, false);
        elem.addEventListener('dragleave', handleDragLeave, false);
        elem.addEventListener('drop', handleDrop, false);
        elem.addEventListener('dragend', handleDragEnd, false);

    }

    var cols = document.querySelectorAll('#columns .wikipage');
    [].forEach.call(cols, addDnDHandlers);
    */
    // debugger;
    // var list = 
    jQuery('#columns').sortable({
        change: function( event, ui ) {

            // console.log("change", event, ui)
        },
        
        update: function( event, ui ) {
            console.log("update", event, ui)

            
            // let targetRoute;
            // if(e.target.localName == "a"){
            //     targetRoute = e.target.href
            // } else {
            //     targetRoute = e.target.querySelector("a").href
            // }
            // let targetParams = new URL(targetRoute)
            // var targetProperties = {}
            // for (const [key, value] of targetParams.searchParams.entries()) {
            //     targetProperties[key] = value
            // }
            // console.log("targetProperties", targetProperties)
            
            var srcParams = new URL(ui.item[0].querySelector("a").href)
            var srcProperties = {}

            for (const [key, value] of srcParams.searchParams.entries()) {
                srcProperties[key] = value
            }
            // console.log("srcProperties", srcProperties)

            let project_id = srcProperties["project_id"]

            // console.log("project_id", project_id)

            let request = {
                "src_wiki_id": srcProperties["wiki_id"],
                "index": ui.item.index()
                // "target_wiki_id": targetProperties["wiki_id"]
            }
            // grab 2nd index item from list, or update api

            console.log("request", request)
        }
    })
    //     {
    //     items: 'li',
    //     toleranceElement: '> div'
    //   });
});