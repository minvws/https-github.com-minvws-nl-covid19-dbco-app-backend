import type { DTO } from '@dbco/schema/dto';

// eslint-disable-next-line @typescript-eslint/ban-types
export type Schema<TIndex extends AnyObject = {}, TContext extends AnyObject = {}, TTask extends AnyObject = {}> = {
    version: number;
    tabs: SchemaTab[];
    // eslint-disable-next-line @typescript-eslint/ban-types
    sidebar: Function;
    rules: SchemaRules<DTO<TIndex>, DTO<TContext>, DTO<TTask>>;
};

type SchemaTab = {
    type: string;
    id: string;
    title: string;
    // eslint-disable-next-line @typescript-eslint/ban-types
    schema: Function;
};

export type SchemaRules<TIndex, TContext, TTask> = {
    index: SchemaRule<TIndex>[];
    context: SchemaRule<TContext>[];
    task: SchemaRule<TTask>[];
};

export type SchemaRule<T> = {
    title: string;
    watch: string | string[];
    callback(data: T, newVal: any[], oldVal: any[]): object;
};
