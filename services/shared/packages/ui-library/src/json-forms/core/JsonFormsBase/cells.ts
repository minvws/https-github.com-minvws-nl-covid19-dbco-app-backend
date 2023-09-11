import type { JsonFormsCellRendererRegistryEntry } from '@jsonforms/core';
import {
    isTimeControl,
    isDateTimeControl,
    isDateControl,
    isIntegerControl,
    isNumberControl,
    isMultiLineControl,
    isStringControl,
    isBooleanControl,
    rankWith,
    isOneOfEnumControl,
    isEnumControl,
    and,
} from '@jsonforms/core';
import { isNonObjectType } from '../../utils';
import {
    BooleanCell,
    TextCell,
    TextareaCell,
    NumberCell,
    IntegerCell,
    DateCell,
    DateTimeCell,
    TimeCell,
    EnumCell,
    OneOfEnumCell,
} from '../../cells';

export const cells: JsonFormsCellRendererRegistryEntry[] = [
    {
        cell: BooleanCell,
        tester: rankWith(1, isBooleanControl),
    },
    {
        cell: TextCell,
        tester: rankWith(1, isStringControl),
    },
    {
        cell: NumberCell,
        tester: rankWith(1, isNumberControl),
    },
    {
        cell: IntegerCell,
        tester: rankWith(1, isIntegerControl),
    },
    {
        cell: EnumCell,
        tester: rankWith(2, and(isEnumControl, isNonObjectType)),
    },
    {
        cell: OneOfEnumCell,
        tester: rankWith(2, and(isOneOfEnumControl, isNonObjectType)),
    },
    {
        cell: TextareaCell,
        tester: rankWith(2, isMultiLineControl),
    },
    {
        cell: DateCell,
        tester: rankWith(2, isDateControl),
    },
    {
        cell: DateTimeCell,
        tester: rankWith(2, isDateTimeControl),
    },
    {
        cell: TimeCell,
        tester: rankWith(2, isTimeControl),
    },
];
