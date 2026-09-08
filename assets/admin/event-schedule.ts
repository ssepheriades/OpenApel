import './event-schedule.css';

function allDayToggle(form: HTMLFormElement): HTMLInputElement | null {
    return form.querySelector<HTMLInputElement>('input[type="checkbox"][name$="[isAllDay]"]');
}

function setScheduleFieldsHidden(root: HTMLElement, hidden: boolean): void {
    root.hidden = hidden;
}

function syncEventSchedule(form: HTMLFormElement): void {
    const toggle = allDayToggle(form);
    if (!toggle) {
        return;
    }

    const allDay = toggle.checked;

    form.querySelectorAll<HTMLElement>('.js-event-timed-field').forEach((el) => {
        setScheduleFieldsHidden(el, allDay);
    });

    form.querySelectorAll<HTMLElement>('.js-event-all-day-field').forEach((el) => {
        setScheduleFieldsHidden(el, !allDay);
    });
}

export function enhanceEventSchedule(): void {
    document.querySelectorAll<HTMLFormElement>('form').forEach((form) => {
        const toggle = allDayToggle(form);
        if (!toggle || toggle.dataset.eventScheduleReady === '1') {
            return;
        }

        toggle.dataset.eventScheduleReady = '1';
        syncEventSchedule(form);
        toggle.addEventListener('change', () => syncEventSchedule(form));
    });
}
