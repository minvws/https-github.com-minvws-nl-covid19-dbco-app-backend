import type { Meta, StoryFn } from '@storybook/vue';
import { Table, TableCaption, TableContainer, Thead, Tbody, Tfoot, Th, Tr, Td } from '.';

const story: Meta = {
    title: 'Components/Table',
    component: Table,
};

export const Default: StoryFn = () => ({
    components: { Table, TableCaption, TableContainer, Thead, Tbody, Tfoot, Th, Tr, Td },
    setup() {},
    template: `
<TableContainer>
    <Table>
      <TableCaption>Imperial to metric conversion factors</TableCaption>
      <Thead>
        <Tr>
          <Th>To convert</Th>
          <Th>into</Th>
          <Th isNumeric>multiply by</Th>
        </Tr>
      </Thead>
      <Tbody>
        <Tr>
          <Td>inches</Td>
          <Td>millimetres (mm)</Td>
          <Td isNumeric>25.4</Td>
        </Tr>
        <Tr>
          <Td>feet</Td>
          <Td>centimetres (cm)</Td>
          <Td isNumeric>30.48</Td>
        </Tr>
        <Tr>
          <Td>yards</Td>
          <Td>metres (m)</Td>
          <Td isNumeric>0.91444</Td>
        </Tr>
      </Tbody>
      <Tfoot>
        <Tr>
          <Th>To convert</Th>
          <Th>into</Th>
          <Th isNumeric>multiply by</Th>
        </Tr>
      </Tfoot>
    </Table>
  </TableContainer>`,
});

export const ExtraWide: StoryFn = () => ({
    components: { Table, TableCaption, TableContainer, Thead, Tbody, Tfoot, Th, Tr, Td },
    setup() {
        return {
            tableData: [
                {
                    a: 'Aperiam nisi',
                    b: 'Asperiores recusandae odit',
                    c: 5,
                    d: 'Animi in labore animi voluptatum quis numquam minus quas voluptatibus blanditiis.',
                    e: 'Modi voluptatem exercitationem porro',
                },
                {
                    a: 'Fugit assumenda',
                    b: 'Eum aliquam',
                    c: 340,
                    d: 'Labore sint a dicta assumenda velit labore fugit assumenda.',
                    e: 'Modi voluptatem exercitationem porro',
                },
                {
                    a: 'Voluptatem porro',
                    b: 'Exercitationem nisi ducimus',
                    c: 4560,
                    d: 'Similique exercitationem nisi ducimus id quisquam sapiente suscipit neque blanditiis reiciendis eum aliquam.',
                    e: 'Modi voluptatem exercitationem porro',
                },
            ],
        };
    },
    template: `
<TableContainer>
    <Table>
      <Thead>
        <Tr>
          <Th>Atque amet</Th>
          <Th>Vitae odit voluptatem</Th>
          <Th isNumeric>Perferendis</Th>
          <Th>Libero numquam</Th>
          <Th>Dignissimos amet</Th>
        </Tr>
      </Thead>
      <Tbody>
        <Tr v-for="data in tableData" :key="data.a">
          <Td>{{ data.a }}</Td>
          <Td>{{ data.b }}</Td>
          <Td isNumeric>{{ data.c }}</Td>
          <Td>{{ data.d }}</Td>
          <Td>{{ data.e }}</Td>
        </Tr>
      </Tbody>
    </Table>
  </TableContainer>`,
});

export default story;
