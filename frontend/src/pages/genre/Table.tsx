import * as React from 'react';
import MUIDataTable, { MUIDataTableColumn } from 'mui-datatables';
import { useEffect } from 'react';
import { useState } from 'react';
import { httpVideo } from '../../util/http';
import { Chip, IconButton } from '@material-ui/core';
import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';
import EditIcon from '@material-ui/icons/Edit';
import {Link} from "react-router-dom";
import { Genre, ListResponse } from '../../util/models';
import DefaultTable from '../../components/Table';

interface Category {
    name: string
}

const columnsDefinition: MUIDataTableColumn[] = [
    {
        name: "id",
        label: "ID",
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
        name: "categories",
        label: "Categories",
        options: {
            customBodyRender(value: Category[], tableMeta, updateValue){
                const categories = value.map((e:Category, i:Number) => e.name).join(', ');
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
                        to={`/genres/${tableMeta.rowData[0]}/edit`}
                    >
                        <EditIcon/>
                    </IconButton>
                )
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

    const [data, setData] = useState<Genre[]>([]);

    useEffect(() => {
        let isSubscribed = true;
        (async function getGenres() {
            const {data} = await httpVideo.get<ListResponse<Genre>>('genres');
            isSubscribed && setData(data.data);
        })();
        return () => {
            isSubscribed = false;
        }
    }, []);

    return (
        <DefaultTable 
            title="Category List"
            columns={columnsDefinition} 
            data={data}
        />
    );
}

export default Table;