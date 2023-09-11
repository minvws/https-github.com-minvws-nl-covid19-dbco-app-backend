import type { CallToActionHistoryItem } from '@dbco/portal-api/callToAction.dto';
import type { CaseTimelineDTO } from '@dbco/portal-api/case.dto';
import { CaseTimelineType } from '@dbco/portal-api/case.dto';
import { fakerjs } from '@/utils/test';

export const generateFakeTimelineItem = (
    type?: CaseTimelineType,
    withNote = false,
    callToActionHistory: Array<CallToActionHistoryItem> = []
): CaseTimelineDTO => ({
    time: fakerjs.date.recent().toISOString(),
    timelineable_id: fakerjs.string.uuid(),
    timelineable_type: type ?? CaseTimelineType.Note,
    title: fakerjs.lorem.sentence(),
    uuid: fakerjs.string.uuid(),
    answer: type === CaseTimelineType.ExpertQuestion ? fakerjs.lorem.paragraph() : undefined,
    answer_time: type === CaseTimelineType.ExpertQuestion ? fakerjs.date.recent().toISOString() : undefined,
    answer_user: type === CaseTimelineType.ExpertQuestion ? fakerjs.person.fullName() : undefined,
    author_user: fakerjs.person.fullName(),
    call_to_action_deadline: type === CaseTimelineType.CallToAction ? fakerjs.date.soon().toISOString() : undefined,
    call_to_action_items: type === CaseTimelineType.CallToAction ? callToActionHistory : undefined,
    call_to_action_uuid: type === CaseTimelineType.CallToAction ? fakerjs.string.uuid() : undefined,
    note: withNote ? fakerjs.lorem.sentence() : undefined,
});

export const fakeTimelineItem: CaseTimelineDTO = generateFakeTimelineItem();
export const fakeTimelineItemWithAnswer: CaseTimelineDTO = generateFakeTimelineItem(CaseTimelineType.ExpertQuestion);
export const fakeTimelineItemWithAnswerAndNote: CaseTimelineDTO = generateFakeTimelineItem(
    CaseTimelineType.ExpertQuestion,
    true
);
export const fakeTimelineItemWithNote: CaseTimelineDTO = generateFakeTimelineItem(undefined, true);
export const fakeTimelineItemWithCallToAction: CaseTimelineDTO = generateFakeTimelineItem(
    CaseTimelineType.CallToAction
);
export const fakeTimelineItemWithCallToActionAndNote: CaseTimelineDTO = generateFakeTimelineItem(
    CaseTimelineType.CallToAction,
    true
);
export const fakeTimelineItemWithCallToActionHistory = (
    callToActionHistory: Array<CallToActionHistoryItem>
): CaseTimelineDTO => generateFakeTimelineItem(CaseTimelineType.CallToAction, false, callToActionHistory);
export const fakeTimelineItemWithCallToActionHistoryAndNote = (
    callToActionHistory: Array<CallToActionHistoryItem>
): CaseTimelineDTO => generateFakeTimelineItem(CaseTimelineType.CallToAction, true, callToActionHistory);
