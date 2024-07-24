
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
