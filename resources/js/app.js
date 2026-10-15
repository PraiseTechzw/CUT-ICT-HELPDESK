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
	Info,
	Database,
	Eye,
	EyeOff,
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
		LockKeyhole,
		KeyRound,
	Pencil,
	Plus,
	RefreshCw,
	Search,
	Save,
	Send,
	ShieldCheck,
	Settings,
	Sun,
	Ticket,
	TicketPlus,
	Tickets,
	Tags,
	UserRoundCog,
	UserRoundCheck,
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
	info: Info,
	database: Database,
	eye: Eye,
	'eye-off': EyeOff,
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
	save: Save,
	send: Send,
	'shield-check': ShieldCheck,
	settings: Settings,
	sun: Sun,
	ticket: Ticket,
	'ticket-plus': TicketPlus,
	tickets: Tickets,
	tags: Tags,
	'user-round-cog': UserRoundCog,
	'user-round-check': UserRoundCheck,
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

document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
	toggle.addEventListener('click', () => {
		const password = document.getElementById(toggle.getAttribute('aria-controls'));
		if (!(password instanceof HTMLInputElement)) return;

		const isVisible = password.type === 'text';
		password.type = isVisible ? 'password' : 'text';
		toggle.setAttribute('aria-label', isVisible ? 'Show password' : 'Hide password');
		toggle.setAttribute('title', isVisible ? 'Show password' : 'Hide password');
		toggle.replaceChildren(createElement(isVisible ? Eye : EyeOff, {
			'aria-hidden': 'true',
			focusable: 'false',
		}));
	});
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

const roleTabs = [...document.querySelectorAll('[data-role-tab]')];

function selectRoleTab(selectedTab) {
	for (const tab of roleTabs) {
		const selected = tab === selectedTab;
		tab.setAttribute('aria-selected', String(selected));
		tab.tabIndex = selected ? 0 : -1;
		const panel = document.querySelector(`[data-role-panel="${tab.dataset.roleTab}"]`);
		if (panel) panel.hidden = !selected;
	}
}

for (const [index, tab] of roleTabs.entries()) {
	tab.addEventListener('click', () => selectRoleTab(tab));
	tab.addEventListener('keydown', (event) => {
		if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
		event.preventDefault();
		const nextIndex = event.key === 'Home' ? 0 : event.key === 'End' ? roleTabs.length - 1 : (index + (event.key === 'ArrowRight' ? 1 : roleTabs.length - 1)) % roleTabs.length;
		roleTabs[nextIndex]?.focus();
		selectRoleTab(roleTabs[nextIndex]);
	});
}

document.querySelectorAll('.user-group-member-search input').forEach((search) => {
	const form = search.closest('form');
	const options = [...(form?.querySelectorAll('.user-group-member-option') ?? [])];
	const emptyMessage = form?.querySelector('.user-group-member-empty');
	search.addEventListener('input', () => {
		const term = search.value.trim().toLocaleLowerCase();
		let visibleCount = 0;
		for (const option of options) {
			const matches = `${option.dataset.userName ?? ''} ${option.dataset.userEmail ?? ''}`.includes(term);
			option.hidden = !matches;
			if (matches) visibleCount++;
		}
		if (emptyMessage) emptyMessage.hidden = visibleCount > 0;
	});
});

const helpdeskShell = document.querySelector('.helpdesk-shell');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebarClose = document.querySelector('[data-sidebar-close]');
const themeToggle = document.querySelector('[data-theme-toggle]');
const ticketSearch = document.querySelector('.workspace-search input');
const isMobileNavigation = window.matchMedia('(max-width: 760px)');

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
