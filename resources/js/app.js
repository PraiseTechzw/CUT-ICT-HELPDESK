import {
	Activity,
	AlarmClock,
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
	Search,
	Send,
	ShieldCheck,
	Settings,
	Sun,
	Ticket,
	TicketPlus,
	Tickets,
	UserRoundCog,
	UsersRound,
	createElement,
} from 'lucide';

const lucideIcons = {
	activity: Activity,
	'alarm-clock': AlarmClock,
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
	search: Search,
	send: Send,
	'shield-check': ShieldCheck,
	settings: Settings,
	sun: Sun,
	ticket: Ticket,
	'ticket-plus': TicketPlus,
	tickets: Tickets,
	'user-round-cog': UserRoundCog,
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
