import * as React from 'react';
import MUIDataTable, { MUIDataTableColumn } from 'mui-datatables';
import { useEffect, useState, useRef } from 'react';
import {IconButton, MuiThemeProvider } from '@material-ui/core';
import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';
import categoryHttp from '../../util/http/category-http';
import EditIcon from '@material-ui/icons/Edit';
import {Link} from "react-router-dom";
import { BadgeNo, BadgeYes } from '../../components/Badge';
import { Category, ListResponse } from '../../util/models';
import DefaultTable, { makeActionStyles, TableColumn } from '../../components/Table';
import { useSnackbar } from 'notistack';

interface Pagination {
    page: number;
    total: number;
    per_page: number;
}

interface SearchState {
    search: string;
    pagination: Pagination;
}

const columnsDefinition: TableColumn[] = [
    {
        name: 'id',
        label: 'ID',
        width: '30%',
        options: {
            sort: false,
            filter: false
        }
    },
    {
        name: "name",
        label: "Name",
        width: '43%'
    },
    {
        name: "is_active",
        label: "Active?",
        options: {
            customBodyRender(value, tableMeta, updateValue){
                return value ? <BadgeYes label={"Yes"}/> : <BadgeNo label={"No"} />
            }
        },
        width: '4%'
    },
    {
        name: "created_at",
        label: "Created At",
        options: {
            customBodyRender(value, tableMeta, updateValue){
                return <span>{format(parseISO(value), 'dd/MM/yyyy')}</span>
            }
        },
        width: '10%'
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

const Table: React.FC = () => {

    const snackbar = useSnackbar(); 
    const subscribed = useRef(true)
    const [data, setData] = useState<Category[]>([]);
    const [loading, setLoading] = useState<boolean>(false);
    const [searchState, setSearchState] = useState<SearchState>({
        search: '',
        pagination: {
            page: 1,
            total: 0,
            per_page: 10,
        }
    });

    useEffect(() => {
        subscribed.current = true;
        getData();
        return () => {
            subscribed.current = false;
        }
    },[
        searchState.search,
        searchState.pagination.page,
        searchState.pagination.per_page
    ]);

    async function getData() {
        setLoading(true);
        try{
            const {data} = await categoryHttp.list<ListResponse<Category>>({
                queryParams: {
                    search: searchState.search,
                    page: searchState.pagination.page,
                    per_page: searchState.pagination.per_page
                }
            });
            if(subscribed.current){
                setData(data.data);
                setSearchState((prevState => ({
                    ...prevState,
                    pagination: {
                        ...prevState.pagination,
                        total: data.meta.total
                    }
                })));
            }
        } catch (error) {
            console.error(error);
            snackbar.enqueueSnackbar(
                'Error trying to save category',
                {variant:"error"}
            );
        } finally {
            setLoading(false);
        }
    }

    return (
        <MuiThemeProvider theme={makeActionStyles(columnsDefinition.length -1)}>
            <DefaultTable 
                title="Category List"
                columns={columnsDefinition} 
                data={data}
                loading={loading}
                options={{
                    serverSide: true,
                    searchText: searchState.search,
                    page: searchState.pagination.page - 1,
                    rowsPerPage: searchState.pagination.per_page,
                    count: searchState.pagination.total,
                    onSearchChange: (value) => setSearchState((prevState => 
                        ({
                            ...prevState,
                            search: value as any
                        })
                    )),
                    onChangePage: (page) => setSearchState((prevState => 
                        ({
                            ...prevState,
                            pagination: {
                                ...prevState.pagination,
                                page: page + 1
                            }
                        })
                    )),
                    onChangeRowsPerPage: (perPage) => setSearchState((prevState => 
                        ({
                            ...prevState,
                            pagination: {
                                ...prevState.pagination,
                                per_page: perPage
                            }
                        })
                    )),
                }}
            />
        </MuiThemeProvider>
    );
}

export default Table;