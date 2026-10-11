import {
	Activity,
	AlarmClock,
	Archive,
	ArrowLeft,
	Bell,
	BookOpen,
	Building2,
	CalendarDays,
	ChartNoAxesColumnIncreasing,
	ChartNoAxesCombined,
	Check,
	ChevronDown,
	ChevronRight,
	CircleCheck,
	CircleAlert,
	Clock3,
	FileText,
	Database,
	Headset,
	LayoutDashboard,
	List,
	ListFilter,
	Mail,
	MapPin,
	Menu,
	Moon,
	NotebookPen,
	Network,
	Package,
	Paperclip,
	Pencil,
	Plus,
	RefreshCw,
	Search,
	Send,
	ShieldCheck,
	Settings,
	Sun,
	Ticket,
	TicketPlus,
	Tickets,
	Tags,
	UserRoundCog,
	UserRoundPlus,
	UserRoundX,
	UsersRound,
	createElement,
} from 'lucide';

const lucideIcons = {
	activity: Activity,
	'alarm-clock': AlarmClock,
	archive: Archive,
	'arrow-left': ArrowLeft,
	bell: Bell,
	'book-open': BookOpen,
	'building-2': Building2,
	'calendar-days': CalendarDays,
	'chart-no-axes-column-increasing': ChartNoAxesColumnIncreasing,
	'chart-no-axes-combined': ChartNoAxesCombined,
	check: Check,
	'chevron-down': ChevronDown,
	'chevron-right': ChevronRight,
	'circle-check': CircleCheck,
	'circle-alert': CircleAlert,
	'clock-3': Clock3,
	'file-text': FileText,
	database: Database,
	headset: Headset,
	'layout-dashboard': LayoutDashboard,
	list: List,
	'list-filter': ListFilter,
	'map-pin': MapPin,
	menu: Menu,
	moon: Moon,
	'notebook-pen': NotebookPen,
	mail: Mail,
	network: Network,
	package: Package,
	paperclip: Paperclip,
	pencil: Pencil,
	plus: Plus,
	'refresh-cw': RefreshCw,
	search: Search,
	send: Send,
	'shield-check': ShieldCheck,
	settings: Settings,
	sun: Sun,
	ticket: Ticket,
	'ticket-plus': TicketPlus,
	tickets: Tickets,
	tags: Tags,
	'user-round-cog': UserRoundCog,
	'user-round-plus': UserRoundPlus,
	'user-round-x': UserRoundX,
	'users-round': UsersRound,
};

document.querySelectorAll('svg[data-lucide]').forEach((placeholder) => {
	const icon = lucideIcons[placeholder.dataset.lucide];
	if (!icon) return;

	placeholder.replaceWith(createElement(icon, {
		class: placeholder.getAttribute('class') ?? '',
		'aria-hidden': placeholder.getAttribute('aria-hidden') ?? 'true',
		focusable: 'false',
	}));
});

document.querySelectorAll('.ticket-overdue-filters select').forEach((filter) => {
	filter.addEventListener('change', () => filter.form?.requestSubmit());
});

const technicianSearch = document.querySelector('[data-technician-search]');
const technicianOptions = [...document.querySelectorAll('.ticket-technician-option[data-technician-name]')];
const technicianNoResults = document.querySelector('[data-technician-no-results]');

technicianSearch?.addEventListener('input', () => {
	const searchTerm = technicianSearch.value.trim().toLocaleLowerCase();
	let visibleCount = 0;

	for (const option of technicianOptions) {
		const matches = option.dataset.technicianName?.includes(searchTerm) ?? false;
		option.hidden = !matches;
		if (matches) visibleCount++;
	}

	if (technicianNoResults) technicianNoResults.hidden = visibleCount > 0;
});

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
