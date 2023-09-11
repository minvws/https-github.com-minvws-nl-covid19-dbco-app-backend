import type { CaseCreateUpdate } from '@dbco/portal-api/case.dto';
import { ExpertQuestionTypeV1 } from '@dbco/enum';
import type { ExpertQuestionRequest, ExpertQuestionResponse } from '@dbco/portal-api/supervision.dto';
import { toDateString } from '../utils/date-format';
import faker from '../utils/faker-decorator';
import type { User } from './login';

export function givenCasePayload(partial?: Partial<CaseCreateUpdate>): CaseCreateUpdate {
    const dateOfBirth = faker.date.past({ years: faker.number.int({ min: 10, max: 60 }) });
    return {
        index: {
            address: {
                postalCode: faker.location.zipCode('####??'),
                street: '',
                houseNumber: faker.location.buildingNumber(),
                houseNumberSuffix: '',
                town: '',
            },
            firstname: faker.person.firstName(),
            lastname: faker.person.lastName(),
            dateOfBirth: toDateString(dateOfBirth),
        },
        contact: {
            phone: '0612345678',
            email: faker.internet.email(),
        },
        test: { dateOfTest: '' },
        general: { hpzoneNumber: null },
        ...partial,
    };
}

export type CreatedCase = {
    general: {
        reference: string;
        hpzoneNumber: string;
    };
    uuid: string;
};

const referrers = {
    gebruiker: '/cases',
    werkverdeler: '/planner',
    gebruikerClusterspecialist: '/cases',
    gebruikerWerkverdeler: '/cases',
} as const;

export const createCaseApi = (data: CaseCreateUpdate = givenCasePayload()) => {
    return cy
        .get<User>('@loggedInUser')
        .then((user) => {
            return referrers[user] ?? '/planner';
        })
        .then((referer) => {
            return cy.authenticatedAPIRequest<{
                data: CreatedCase;
            }>({
                url: '/api/cases',
                method: 'POST',
                headers: {
                    Referer: referer,
                },
                body: data,
            });
        })
        .then((response) => response.body.data);
};

export const addQuestionToCase = (
    uuid: string,
    {
        type = faker.helpers.arrayElement(Object.values(ExpertQuestionTypeV1)),
        question = faker.lorem.words(20),
        subject = faker.lorem.words(2),
        phone = '0612345678',
    }: Partial<ExpertQuestionRequest>
) => {
    return cy.authenticatedAPIRequest<ExpertQuestionResponse>({
        method: 'POST',
        url: `/api/case/${uuid}/expertQuestion`,
        body: {
            type,
            phone,
            subject,
            question,
        },
    });
};
