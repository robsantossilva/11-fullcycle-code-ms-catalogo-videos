import * as React from 'react';
import MUIDataTable, { MUIDataTableColumn } from 'mui-datatables';
import { useEffect } from 'react';
import { useState } from 'react';
import { httpVideo } from '../../util/http';
import { Chip } from '@material-ui/core';
import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';

interface Category {
    name: string
}

const columnsDefinition: MUIDataTableColumn[] = [
    {
        name: "name",
        label: "Name"
    },
    {
        name: "categories",
        label: "Categories",
        options: {
            customBodyRender(value: Category[], tableMeta, updateValue){
                const categories = value.map((e:Category, i:Number) => {
                    return e.name;
                }).join(', ');
                return categories;
            }
        }
    },
    {
        name: "is_active",
        label: "Is Active",
        options: {
            customBodyRender(value, tableMeta, updateValue){
                return value ? <Chip label="Active" color="primary" /> : <Chip label="Inactive" color="secondary" />
            }
        }
    },
    {
        name: "created_at",
        label: "Created At",
        options: {
            customBodyRender(value, tableMeta, updateValue){
                return <span>{format(parseISO(value), 'dd/MM/yyyy')}</span>
            }
        }
    }
];

const data = [
    {name: "Test1", is_active:true, created_at:"2021-06-15"}
]

type TableProps = {

};

const Table: React.FC = (props: TableProps) => {

    const [data, setData] = useState([]);

    useEffect(() => {
        httpVideo.get('genres').then(
            response => {
                setData(response.data.data)
            }
        )
    }, []);

    return (
        <MUIDataTable 
            title="Category List"
            columns={columnsDefinition} 
            data={data}
        />
    );
}

export default Table;