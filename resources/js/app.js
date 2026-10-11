const helpdeskShell = document.querySelector('.helpdesk-shell');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebarClose = document.querySelector('[data-sidebar-close]');

function closeSidebar() {
	helpdeskShell?.classList.remove('sidebar-open');
	sidebarToggle?.setAttribute('aria-expanded', 'false');
}

sidebarToggle?.addEventListener('click', () => {
	const isOpen = helpdeskShell?.classList.toggle('sidebar-open') ?? false;
	sidebarToggle.setAttribute('aria-expanded', String(isOpen));
});

sidebarClose?.addEventListener('click', closeSidebar);

document.addEventListener('keydown', (event) => {
	if (event.key === 'Escape') closeSidebar();
});
