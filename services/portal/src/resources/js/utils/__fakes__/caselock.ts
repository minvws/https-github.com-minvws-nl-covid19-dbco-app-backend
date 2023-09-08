import type { CaseLockResponse } from '@dbco/portal-api/case.dto';
import { fakerjs } from '../test';

export const generateFakeCaseLockResponse = (locked = false, noUser = true): CaseLockResponse => ({
    status: locked ? 200 : 204,
    data: noUser
        ? {}
        : {
              user: {
                  name: fakerjs.person.fullName(),
                  organisation: fakerjs.company.name(),
              },
          },
});

export const fakeCaseLockResponseLocked: CaseLockResponse = generateFakeCaseLockResponse(true, false);

export const fakeCaseLockResponseLockedNoUser: CaseLockResponse = generateFakeCaseLockResponse(true);

export const fakeCaseLockResponseUnlocked: CaseLockResponse = generateFakeCaseLockResponse();
