/**
 * Copy thead labels onto tbody cells so narrow-screen CSS can render rows as cards.
 */
(function () {
    function labelAdminTables(root) {
        (root || document).querySelectorAll('.admin-table-scroll table').forEach(function (table) {
            var headers = Array.prototype.map.call(table.querySelectorAll('thead th'), function (th) {
                return (th.textContent || '').replace(/\s+/g, ' ').trim();
            });

            if (! headers.length) {
                return;
            }

            Array.prototype.forEach.call(table.querySelectorAll('tbody tr'), function (tr) {
                var cells = Array.prototype.slice.call(tr.children);

                if (cells.length === 1 && cells[0].hasAttribute('colspan')) {
                    cells[0].classList.add('admin-table-empty');
                    return;
                }

                cells.forEach(function (td, index) {
                    if (headers[index]) {
                        td.setAttribute('data-label', headers[index]);
                    }
                });
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            labelAdminTables();
        });
    } else {
        labelAdminTables();
    }

    window.coinAdminLabelTables = labelAdminTables;
})();
