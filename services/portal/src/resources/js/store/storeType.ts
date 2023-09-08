import type { AppStoreState } from './app/appStore';
import type { ChoreStoreState } from './chore/choreStore';

export enum StoreType {
    CONTEXT = 'context',
    INDEX = 'index',
    ORGANISATION = 'organisation',
    PLACE = 'place',
    PLANNER = 'planner',
    SUPERVISION = 'supervision',
    TASK = 'task',
    USERINFO = 'userInfo',
}

export type PiniaStateTree = {
    app: AppStoreState;
    chore: ChoreStoreState;
};
