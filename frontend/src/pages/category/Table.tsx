import * as React from 'react';
import MUIDataTable, { MUIDataTableColumn } from 'mui-datatables';
import { useEffect } from 'react';
import { useState } from 'react';
import { Chip, IconButton } from '@material-ui/core';
import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';
import categoryHttp from '../../util/http/category-http';
import EditIcon from '@material-ui/icons/Edit';
import {Link} from "react-router-dom";
import { BadgeNo, BadgeYes } from '../../components/Badge';


const columnsDefinition: MUIDataTableColumn[] = [
    {
        name: 'id',
        label: 'ID',
        //width: '30%',
        options: {
            sort: false,
            filter: false
        }
    },
    {
        name: "name",
        label: "Name"
    },
    {
        name: "is_active",
        label: "Is Active",
        options: {
            customBodyRender(value, tableMeta, updateValue){
                return value ? <BadgeYes label={"Yes"}/> : <BadgeNo label={"No"} />
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
    },
    {
        name: "actions",
        label: "Ações",
        options: {
            sort: false,
            filter: false,
            customBodyRender: (value, tableMeta) => {
                return (
                    <IconButton
                        color={'secondary'}
                        component={Link}
                        to={`/categories/${tableMeta.rowData[0]}/edit`}
                    >
                        <EditIcon/>
                    </IconButton>
                )
            }
        }
    }
];

const data = [
    {name: "Test1", is_active:true, created_at:"2021-06-15"},
    {name: "Test1", is_active:false, created_at:"2021-06-16"},
    {name: "Test1", is_active:true, created_at:"2021-06-17"}
]


interface Category {
    id: string;
    name: string;
    is_active: string;
    created_at: string;
}

type TableProps = {

};

const Table: React.FC = (props: TableProps) => {

    const [data, setData] = useState<Category[]>([]);

    useEffect(() => {
        categoryHttp.list<{data: Category[]}>().then(
            ({data}) => {
                setData(data.data);
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