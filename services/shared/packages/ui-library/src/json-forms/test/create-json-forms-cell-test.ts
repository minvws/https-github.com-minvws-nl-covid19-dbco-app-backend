import { SingleCellControl } from '../controls';
import type { Component } from 'vue';
import { cells } from '../core/JsonFormsBase/cells';
import type { JsonFormsBaseTestConfig } from './create-json-forms-base-test';
import { createJsonFormsBaseTest } from './create-json-forms-base-test';

export interface JsonFormsCellTestConfig extends JsonFormsBaseTestConfig {
    cell: Component;
}

export function createJsonFormsCellTest({ cell, ...rest }: JsonFormsCellTestConfig) {
    const filteredCells = cells.filter((registry) => registry.cell === cell);

    if (!filteredCells.length) {
        throw new Error(
            `No cell registry was found! Either the cell is not registered or the tester did not match the schema.`
        );
    }

    return createJsonFormsBaseTest({
        ...rest,
        cells: filteredCells,
        renderers: [
            {
                renderer: SingleCellControl,
                tester: () => 999,
            },
        ],
    });
}
