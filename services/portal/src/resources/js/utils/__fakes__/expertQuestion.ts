import type { ExpertQuestionResponse } from '@dbco/portal-api/supervision.dto';
import { Role } from '@dbco/portal-api/user';
import { fakerjs } from '@/utils/test';
import { ExpertQuestionTypeV1 } from '@dbco/enum';

export const generateFakeAssignedUser = (role?: Role): ExpertQuestionResponse['assignedUser'] => ({
    name: fakerjs.person.fullName(),
    roles: [role ?? Role.conversation_coach],
    uuid: fakerjs.string.uuid(),
});

export const generateFakeAnswer = (): ExpertQuestionResponse['answer'] => ({
    createdAt: fakerjs.date.recent().toISOString(),
    value: fakerjs.lorem.paragraph(),
    answeredBy: generateFakeAssignedUser(),
});

export const generateFakeExpertQuestionResponse = (
    answered = false,
    assigned = false,
    type?: ExpertQuestionTypeV1
): ExpertQuestionResponse => ({
    caseUuid: fakerjs.string.uuid(),
    createdAt: fakerjs.date.past().toISOString(),
    assignedUser: assigned ? generateFakeAssignedUser() : null,
    phone: fakerjs.phone.number(),
    question: fakerjs.lorem.sentence(),
    subject: fakerjs.lorem.words(),
    type: type ?? ExpertQuestionTypeV1.VALUE_conversation_coach,
    updatedAt: fakerjs.date.recent().toISOString(),
    user: {
        name: fakerjs.person.fullName(),
        roles: [Role.user],
        uuid: fakerjs.string.uuid(),
    },
    uuid: fakerjs.string.uuid(),
    caseOrganisationName: fakerjs.company.name(),
    answer: answered ? generateFakeAnswer() : null,
});
