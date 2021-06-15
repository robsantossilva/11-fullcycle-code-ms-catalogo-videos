import * as React from 'react';
import MUIDataTable, { MUIDataTableColumn } from 'mui-datatables';
import { useEffect } from 'react';
import { useState } from 'react';
import { httpVideo } from '../../util/http';
import { Chip } from '@material-ui/core';
import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';


const columnsDefinition: MUIDataTableColumn[] = [
    {
        name: "name",
        label: "Name"
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
    {name: "Test1", is_active:true, created_at:"2021-06-15"},
    {name: "Test1", is_active:false, created_at:"2021-06-16"},
    {name: "Test1", is_active:true, created_at:"2021-06-17"}
]

type TableProps = {

};

const Table: React.FC = (props: TableProps) => {

    const [data, setData] = useState([]);

    useEffect(() => {
        httpVideo.get('categories').then(
            response => {
                setData(response.data.data)
                console.log(response.data.data)
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