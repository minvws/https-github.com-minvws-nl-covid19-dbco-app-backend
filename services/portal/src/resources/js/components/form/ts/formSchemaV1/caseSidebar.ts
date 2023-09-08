import store from '@/store';
import { SchemaGenerator } from '@/formSchemas/schemaGenerator';
import { FormConditionRule } from '../formTypes';
import type { AllowedVersions } from '.';
import Calendar from '@/components/caseEditor/Calendar/Calendar.vue';
import { useCalendarStore } from '@/store/calendar/calendarStore';
import { CalendarViewV1 } from '@dbco/enum';

export const sidebarSchema = <TIndex extends AllowedVersions['index']>() => {
    const generator = new SchemaGenerator<TIndex>();
    const calendar = useCalendarStore();
    const ranges = calendar.getCalendarDataByView(CalendarViewV1.VALUE_index_sidebar);
    const meta = store.getters['index/meta'];

    return generator.toConfig([
        generator.slot(
            [
                generator.info(
                    'De bron- en/of besmettelijke periode kunnen nog niet worden getoond. Vul minimaal in: klachten, EZD, testdatum.',
                    true,
                    12,
                    'warning',
                    'px-0 info-block--lg'
                ),
            ],
            [
                {
                    rule: FormConditionRule.MedicalPeriodInfoIncomplete,
                },
            ]
        ),
        generator.slot(
            [
                generator.info(
                    'Vul voor definitieve besmettelijke periode minimaal in: wel/geen klachten en ziekenhuisopname.',
                    true,
                    12,
                    'warning',
                    'px-0 info-block--lg'
                ),
            ],
            [
                {
                    rule: FormConditionRule.MedicalPeriodInfoNotDefinitive,
                },
            ]
        ),
        generator.component(Calendar, {
            class: 'px-0',
            showLegend: true,
            ranges,
            defaultMaxDate: meta.completedAt,
        }),
        generator
            .field('general', 'notes')
            .textArea(undefined, 'Je kunt hier een notitie maken', 12, 'px-0')
            .appendConfig({ maxlength: 5000 }),
    ]);
};
