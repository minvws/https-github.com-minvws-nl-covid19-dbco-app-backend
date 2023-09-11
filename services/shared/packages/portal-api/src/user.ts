export enum Role {
    admin = 'admin',
    casequality = 'casequality',
    casequality_nationwide = 'casequality_nationwide',
    compliance = 'compliance',
    contextmanager = 'contextmanager',
    conversation_coach = 'conversation_coach',
    conversation_coach_nationwide = 'conversation_coach_nationwide',
    medical_supervisor = 'medical_supervisor',
    medical_supervisor_nationwide = 'medical_supervisor_nationwide',
    planner = 'planner',
    planner_nationwide = 'planner_nationwide',
    user = 'user',
    user_nationwide = 'user_nationwide',
    user_planner = 'user_planner',
}

export enum SupervisionRoles {
    MEDICAL_SUPERVISION = 'medical-supervision',
    CONVERSATION_COACH = 'conversation-coach',
}

export type User = {
    name: string;
    roles: Role[];
    uuid: string;
};
