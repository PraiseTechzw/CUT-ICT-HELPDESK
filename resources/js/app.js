const helpdeskShell = document.querySelector('.helpdesk-shell');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebarClose = document.querySelector('[data-sidebar-close]');
const themeToggle = document.querySelector('[data-theme-toggle]');
const ticketSearch = document.querySelector('.workspace-search input');
const isMobileNavigation = window.matchMedia('(max-width: 680px)');

function applyTheme(theme) {
	document.body.dataset.theme = theme;
	themeToggle?.setAttribute('aria-label', theme === 'dark' ? 'Switch to light appearance' : 'Switch to dark appearance');
	themeToggle?.setAttribute('title', theme === 'dark' ? 'Switch to light appearance' : 'Switch to dark appearance');
}

applyTheme(localStorage.getItem('helpdesk-theme') === 'dark' ? 'dark' : 'light');

themeToggle?.addEventListener('click', () => {
	const theme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
	localStorage.setItem('helpdesk-theme', theme);
	applyTheme(theme);
});

function closeSidebar() {
	helpdeskShell?.classList.remove('sidebar-open');
	updateSidebarToggle();
}

function updateSidebarToggle() {
	if (!sidebarToggle || !helpdeskShell) return;

	if (isMobileNavigation.matches) {
		const isOpen = helpdeskShell.classList.contains('sidebar-open');
		sidebarToggle.setAttribute('aria-expanded', String(isOpen));
		sidebarToggle.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
		sidebarToggle.setAttribute('title', isOpen ? 'Close navigation' : 'Open navigation');
		return;
	}

	const isExpanded = !helpdeskShell.classList.contains('sidebar-collapsed');
	sidebarToggle.setAttribute('aria-expanded', String(isExpanded));
	sidebarToggle.setAttribute('aria-label', isExpanded ? 'Collapse navigation' : 'Expand navigation');
	sidebarToggle.setAttribute('title', isExpanded ? 'Collapse navigation' : 'Expand navigation');
}

if (!isMobileNavigation.matches && localStorage.getItem('helpdesk-sidebar-collapsed') === 'true') {
	helpdeskShell?.classList.add('sidebar-collapsed');
}

document.querySelectorAll('.sidebar-link').forEach((link) => {
	const label = [...link.children].find((child) => child.tagName === 'SPAN' && !child.classList.contains('sidebar-chevron'))?.textContent.trim();
	if (label) link.setAttribute('title', label);
});

sidebarToggle?.addEventListener('click', () => {
	if (!helpdeskShell) return;

	if (isMobileNavigation.matches) {
		helpdeskShell.classList.toggle('sidebar-open');
	} else {
		const isCollapsed = helpdeskShell.classList.toggle('sidebar-collapsed');
		localStorage.setItem('helpdesk-sidebar-collapsed', String(isCollapsed));
	}
	updateSidebarToggle();
});

document.querySelectorAll('.sidebar-group > summary').forEach((summary) => {
	summary.addEventListener('click', (event) => {
		if (!isMobileNavigation.matches && helpdeskShell?.classList.contains('sidebar-collapsed')) {
			event.preventDefault();
			helpdeskShell.classList.remove('sidebar-collapsed');
			localStorage.setItem('helpdesk-sidebar-collapsed', 'false');
			summary.parentElement.open = true;
			updateSidebarToggle();
		}
	});
});

isMobileNavigation.addEventListener('change', () => {
	if (isMobileNavigation.matches) {
		helpdeskShell?.classList.remove('sidebar-collapsed', 'sidebar-open');
	} else if (localStorage.getItem('helpdesk-sidebar-collapsed') === 'true') {
		helpdeskShell?.classList.add('sidebar-collapsed');
	}
	updateSidebarToggle();
});

updateSidebarToggle();

sidebarClose?.addEventListener('click', closeSidebar);

document.addEventListener('keydown', (event) => {
	if (event.key === 'Escape') closeSidebar();
	if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k' && ticketSearch && !ticketSearch.disabled) {
		event.preventDefault();
		ticketSearch.focus();
	}
});
