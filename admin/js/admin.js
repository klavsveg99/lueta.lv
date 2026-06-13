document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('adminSidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var close = document.getElementById('sidebarClose');

    function openSidebar() {
        if (sidebar) sidebar.classList.add('open');
        if (overlay) overlay.classList.add('open');
    }
    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
    }

    if (toggle) toggle.addEventListener('click', openSidebar);
    if (close) close.addEventListener('click', closeSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);

    var msgs = document.querySelectorAll('.msg');
    msgs.forEach(function(m) {
        setTimeout(function() {
            m.style.opacity = '0';
            m.style.transition = 'opacity .5s';
            setTimeout(function() { m.style.display = 'none'; }, 500);
        }, 4000);
    });

    // Icon picker
    var pickers = document.querySelectorAll('.icon-picker');
    pickers.forEach(function(picker) {
        var input = picker.parentElement.querySelector('.icon-input');
        var options = picker.querySelectorAll('.icon-option');

        options.forEach(function(opt) {
            opt.addEventListener('click', function() {
                options.forEach(function(o) { o.classList.remove('active'); });
                this.classList.add('active');
                if (input) input.value = this.dataset.icon;
            });
        });
    });
});
