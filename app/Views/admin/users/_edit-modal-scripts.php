<script>
document.addEventListener('DOMContentLoaded', function() {
    const editUrlPrefix = '<?= url('/admin/users') ?>';
    
    // Determine the default base URL for this page (students, faculty, administrators, or dashboard)
    function getPageBaseUrl() {
        const path = window.location.pathname;
        if (path.indexOf('/admin/faculty') !== -1 || path.indexOf('/admin/staff') !== -1) {
            return '<?= url('/admin/faculty') ?>';
        }
        if (path.indexOf('/admin/administrators') !== -1) {
            return '<?= url('/admin/administrators') ?>';
        }
        if (path.indexOf('/admin/dashboard') !== -1) {
            return '<?= url('/admin/dashboard') ?>';
        }
        return '<?= url('/admin/students') ?>';
    }

    const baseListingUrl = getPageBaseUrl();

    function initUserEditModal(modalEl) {
        if (!modalEl || modalEl.dataset.initialized) return;
        modalEl.dataset.initialized = 'true';

        const uid = modalEl.id.replace('editUserModal', '');
        const regRadio = modalEl.querySelector('#status_reg_' + uid);
        const irregRadio = modalEl.querySelector('#status_irreg_' + uid);
        const yearSelect = modalEl.querySelector('#year_level_' + uid);
        const setSelect = modalEl.querySelector('#set_id_' + uid);
        const setCol = modalEl.querySelector('#setSectionCol_' + uid);
        const irregNotice = modalEl.querySelector('#irregularNotice_' + uid);
        const noSetsAlert = modalEl.querySelector('#noSetsAlert_' + uid);
        const noSetsYear = modalEl.querySelector('#noSetsYear_' + uid);

        function filterSets(resetVal) {
            if (!yearSelect || !setSelect) return;
            const selectedYear = yearSelect.value;
            let available = 0;
            let currentMatches = false;

            if (resetVal) {
                setSelect.value = '';
            }

            for (let i = 0; i < setSelect.options.length; i++) {
                const opt = setSelect.options[i];
                const optYear = opt.getAttribute('data-year-level');
                if (!optYear) {
                    opt.hidden = false;
                    opt.disabled = false;
                    continue;
                }
                if (optYear === selectedYear) {
                    opt.hidden = false;
                    opt.disabled = false;
                    available++;
                    if (opt.value === setSelect.value) {
                        currentMatches = true;
                    }
                } else {
                    opt.hidden = true;
                    opt.disabled = true;
                }
            }

            if (setSelect.value && !currentMatches) {
                setSelect.value = '';
            }

            if (noSetsAlert) {
                if (available === 0) {
                    if (noSetsYear) noSetsYear.textContent = selectedYear;
                    noSetsAlert.classList.remove('d-none');
                } else {
                    noSetsAlert.classList.add('d-none');
                }
            }
        }

        function updateStatusUI() {
            if (!regRadio || !irregRadio) return;
            const isReg = regRadio.checked;
            if (isReg) {
                if (setCol) setCol.classList.remove('d-none');
                if (irregNotice) irregNotice.classList.add('d-none');
                if (setSelect) setSelect.required = true;
                filterSets(false);
            } else {
                if (setCol) setCol.classList.add('d-none');
                if (irregNotice) irregNotice.classList.remove('d-none');
                if (setSelect) {
                    setSelect.value = '';
                    setSelect.required = false;
                }
            }
        }

        if (regRadio) regRadio.addEventListener('change', updateStatusUI);
        if (irregRadio) irregRadio.addEventListener('change', updateStatusUI);
        if (yearSelect) {
            yearSelect.addEventListener('change', function() {
                filterSets(true);
            });
        }

        // Initialize state
        updateStatusUI();

        // Update URL in address bar to /admin/users/{id}/edit when modal opens
        modalEl.addEventListener('show.bs.modal', function() {
            document.documentElement.classList.add('modal-open');
            const targetUrl = editUrlPrefix + '/' + uid + '/edit';
            if (window.location.pathname !== new URL(targetUrl, window.location.origin).pathname) {
                history.pushState({ modalUserId: uid }, '', targetUrl);
            }
        });

        // Revert URL in address bar to base listing URL when modal is dismissed
        modalEl.addEventListener('hidden.bs.modal', function() {
            if (!document.querySelector('.modal.show')) {
                document.documentElement.classList.remove('modal-open');
            }
            const path = window.location.pathname;
            if (/\/admin\/users\/\d+\/edit\/?$/.test(path)) {
                history.replaceState(null, '', baseListingUrl);
            }
        });
    }

    // Attach to all edit user modals on page
    document.querySelectorAll('[id^="editUserModal"]').forEach(function(modalEl) {
        initUserEditModal(modalEl);
    });

    // Handle browser back / forward navigation
    window.addEventListener('popstate', function() {
        const match = window.location.pathname.match(/\/admin\/users\/(\d+)\/edit\/?$/);
        if (match) {
            const targetModal = document.getElementById('editUserModal' + match[1]);
            if (targetModal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(targetModal).show();
            }
        } else {
            // Close any currently visible edit user modals
            document.querySelectorAll('[id^="editUserModal"].show').forEach(function(openModal) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getInstance(openModal)?.hide();
                }
            });
        }
    });

    // Auto-open modal on initial page load if URL is /admin/users/{id}/edit or has ?edit={id}
    try {
        let openId = null;
        const match = window.location.pathname.match(/\/admin\/users\/(\d+)\/edit\/?$/);
        if (match) {
            openId = match[1];
        } else {
            const urlParams = new URLSearchParams(window.location.search);
            openId = urlParams.get('edit');
        }

        if (openId) {
            const targetModal = document.getElementById('editUserModal' + openId);
            if (targetModal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(targetModal);
                modalInstance.show();
            }
        }
    } catch (e) {
        console.error('Error auto-opening edit modal:', e);
    }
});
</script>
