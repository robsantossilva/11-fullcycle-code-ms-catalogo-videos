import * as React from 'react';
import MUIDataTable, { MUIDataTableColumn } from 'mui-datatables';
import { useEffect } from 'react';
import { useState } from 'react';
import { httpVideo } from '../../util/http';
import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';
import { IconButton } from '@material-ui/core';
import EditIcon from '@material-ui/icons/Edit';
import {Link} from "react-router-dom";
import { CastMember, ListResponse } from '../../util/models';
import DefaultTable from '../../components/Table';

const CastMemberTypeMap = {
    1: 'Director',
    2: 'Actor'
}

const columnsDefinition: MUIDataTableColumn[] = [
    {
        name: "id",
        label:"ID",
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
        name: "type_name",
        label: "Type",
        options: {
            customBodyRender(value, tableMeta, updateValue){
                let i = value.split('-')[0];
                return <span>{CastMemberTypeMap[i]}</span> 
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
                        to={`/cast-members/${tableMeta.rowData[0]}/edit`}
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

type TableProps = {

};

const Table: React.FC = (props: TableProps) => {

    const [data, setData] = useState<CastMember[]>([]);

    useEffect(() => {
        let isSubscribed = true;
        (async function getCastMembers(){
            const {data} = await httpVideo.get<ListResponse<CastMember>>('cast_members');
            isSubscribed && setData(data.data);
        })();
        return () => {
            isSubscribed = false;
        }
    }, []);

    return (
        <DefaultTable 
            title="Member List"
            columns={columnsDefinition} 
            data={data}
        />
    );
}

export default Table;