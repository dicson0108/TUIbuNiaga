/**
 * Main Interactive JS
 * Taylor's University Event Management & Ibu Niaga Platform
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Table live search filtering
  const searchInputs = document.querySelectorAll('.table-search-input');
  searchInputs.forEach(input => {
    input.addEventListener('keyup', function () {
      const filter = this.value.toLowerCase();
      const tableWrapper = this.closest('.table-wrapper') || document.querySelector('.table-wrapper');
      if (!tableWrapper) return;
      const rows = tableWrapper.querySelectorAll('tbody tr');
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });
  });

  // 2. Tab switching
  const tabButtons = document.querySelectorAll('[data-tab-target]');
  tabButtons.forEach(btn => {
    btn.addEventListener('click', function () {
      const targetId = this.getAttribute('data-tab-target');
      const container = this.closest('.tabs-container') || document;
      
      // Toggle button active classes
      container.querySelectorAll('[data-tab-target]').forEach(b => b.classList.remove('active'));
      this.classList.add('active');

      // Toggle panels
      container.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
      const activePanel = container.querySelector(targetId);
      if (activePanel) activePanel.style.display = 'block';
    });
  });

  // 3. Confirm dialogs on delete or destructive operations
  const confirmActions = document.querySelectorAll('[data-confirm]');
  confirmActions.forEach(el => {
    el.addEventListener('click', function (e) {
      const message = this.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
      if (!confirm(message)) {
        e.preventDefault();
      }
    });
  });

  // 4. Modal toggles with backdrop click-to-close & Escape key
  const modalTriggers = document.querySelectorAll('[data-modal-open]');
  modalTriggers.forEach(btn => {
    btn.addEventListener('click', function () {
      const modalId = this.getAttribute('data-modal-open');
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
      }
    });
  });

  const modalCloses = document.querySelectorAll('[data-modal-close]');
  modalCloses.forEach(btn => {
    btn.addEventListener('click', function () {
      const modal = this.closest('.modal-overlay');
      if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
      }
    });
  });

  // Close modal when clicking directly on overlay backdrop
  const modalOverlays = document.querySelectorAll('.modal-overlay');
  modalOverlays.forEach(overlay => {
    overlay.addEventListener('click', function (e) {
      if (e.target === this) {
        this.style.display = 'none';
        document.body.style.overflow = '';
      }
    });
  });

  // 5. CSV Table Exporter
  window.exportTableToCSV = function (tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    let csv = [];
    const rows = table.querySelectorAll('tr');
    rows.forEach(row => {
      const cols = row.querySelectorAll('td, th');
      let rowData = [];
      cols.forEach(col => {
        let text = col.innerText.replace(/"/g, '""').trim();
        rowData.push('"' + text + '"');
      });
      csv.push(rowData.join(','));
    });

    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const downloadLink = document.createElement('a');
    downloadLink.download = filename || 'export.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
  };

  // 6. Language Dropdown Menu Handler
  const langTrigger = document.getElementById('langDropdownTrigger');
  const langMenu = document.getElementById('langDropdownMenu');

  if (langTrigger && langMenu) {
    langTrigger.addEventListener('click', function (e) {
      e.stopPropagation();
      const isExpanded = langMenu.classList.toggle('show');
      langTrigger.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
    });

    // Close when clicking outside
    document.addEventListener('click', function (e) {
      if (!langTrigger.contains(e.target) && !langMenu.contains(e.target)) {
        langMenu.classList.remove('show');
        langTrigger.setAttribute('aria-expanded', 'false');
      }
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && langMenu.classList.contains('show')) {
        langMenu.classList.remove('show');
        langTrigger.setAttribute('aria-expanded', 'false');
        langTrigger.focus();
      }
    });
  }

  // 7. Floating Back-to-Top Button Handler
  const backToTopBtn = document.getElementById('back-to-top');
  if (backToTopBtn) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 300) {
        backToTopBtn.classList.add('visible');
      } else {
        backToTopBtn.classList.remove('visible');
      }
    }, { passive: true });

    backToTopBtn.addEventListener('click', function () {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

  // 8. Apple-style Scroll Depth for Header & Topbars
  const publicHeader = document.querySelector('.public-header');
  const workspaceTopbar = document.querySelector('.workspace-topbar');
  const updateScrollDepth = () => {
    const isScrolled = window.scrollY > 12;
    if (publicHeader) {
      publicHeader.classList.toggle('scrolled', isScrolled);
    }
    if (workspaceTopbar) {
      workspaceTopbar.style.boxShadow = isScrolled ? '0 4px 16px rgba(0, 0, 0, 0.04)' : 'none';
    }
  };
  window.addEventListener('scroll', updateScrollDepth, { passive: true });
  updateScrollDepth();

  // 9. Apple Fluid Mobile Navigation Drawer
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const mobileMenuCloseBtn = document.getElementById('mobileMenuCloseBtn');
  const mobileNavDrawer = document.getElementById('mobileNavDrawer');
  const mobileNavBackdrop = document.getElementById('mobileNavBackdrop');

  function openMobileNav() {
    if (mobileNavDrawer && mobileNavBackdrop) {
      mobileNavDrawer.classList.add('open');
      mobileNavBackdrop.classList.add('open');
      document.body.style.overflow = 'hidden';
      if (mobileMenuBtn) mobileMenuBtn.setAttribute('aria-expanded', 'true');
    }
  }

  function closeMobileNav() {
    if (mobileNavDrawer && mobileNavBackdrop) {
      mobileNavDrawer.classList.remove('open');
      mobileNavBackdrop.classList.remove('open');
      document.body.style.overflow = '';
      if (mobileMenuBtn) mobileMenuBtn.setAttribute('aria-expanded', 'false');
    }
  }

  if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', openMobileNav);
  if (mobileMenuCloseBtn) mobileMenuCloseBtn.addEventListener('click', closeMobileNav);
  if (mobileNavBackdrop) mobileNavBackdrop.addEventListener('click', closeMobileNav);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      if (mobileNavDrawer && mobileNavDrawer.classList.contains('open')) {
        closeMobileNav();
      }
      modalOverlays.forEach(overlay => {
        if (overlay.style.display === 'flex') {
          overlay.style.display = 'none';
          document.body.style.overflow = '';
        }
      });
    }
  });
});

