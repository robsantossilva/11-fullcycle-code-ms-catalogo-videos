import * as React from 'react';
import { useContext, useEffect, useRef, useState } from 'react';
import castMemberHttp from "../../util/http/cast-member-http";
import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';
import { IconButton, MuiThemeProvider } from '@material-ui/core';
import EditIcon from '@material-ui/icons/Edit';
import {Link} from "react-router-dom";
import { CastMember, CastMemberTypeMap, ListResponse } from '../../util/models';
import DefaultTable, { makeActionStyles, MuiDataTableRefComponent, TableColumn } from '../../components/Table';
import { useSnackbar } from 'notistack';
import useFilter from '../../hooks/useFilter';
import { FilterResetButton } from '../../components/Table/FilterResetButton';
import * as yup from '../../util/vendor/yup';
import { invert } from 'lodash';
import LoadingContext from '../../components/loading/LoadingContext';
import { useMemo } from 'react';

const castMemberNames = Object.values(CastMemberTypeMap);

const columnsDefinition: TableColumn[] = [
    {
        name: "id",
        label:"ID",
        width: '30%',
        options: {
            sort: false,
            filter: false
        }
    },
    {
        name: "name",
        label: "Name",
        width: '43%',
        options: {
            filter: false
        }
    },
    {
        name: "type",
        label: "Type",
        options: {
            filterOptions: {
                names: castMemberNames,
            },
            customBodyRender: (value, tableMeta, updateValue) => {
                return CastMemberTypeMap[value];
            }
        }
    },
    {
        name: "created_at",
        label: "Created At",
        options: {
            filter: false,
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

const debounceTime = 300;
const debouncedSearchTime = 300;
const rowsPerPage = 15;
const rowsPerPageOptions = [15, 25, 50];

const Table = () => {
    const snackbar = useSnackbar();
    const subscribed = useRef(true)
    const [data, setData] = useState<CastMember[]>([]);
    const loading = useContext(LoadingContext)
    const tableRef = useRef() as React.MutableRefObject<MuiDataTableRefComponent>;

    const extraFilter = useMemo(() => ({
        createValidationSchema: () => {
            return yup.object().shape({
            type: yup
                .string()
                .nullable()
                .transform((value) => {
                return !value || !castMemberNames.includes(value)
                    ? undefined
                    : value;
                })
                .default(null),
            });
        },
        formatSearchParams: (debouncedState) => {
            return debouncedState.extraFilter
            ? {
                ...(debouncedState.extraFilter.type && {
                    type: debouncedState.extraFilter.type,
                }),
                }
            : undefined;
        },
        getStateFromURL: (queryParams) => {
            return {
            type: queryParams.get("type"),
            };
        }
    }) ,[]);

    const {
        columns,
        filterManager,
        filterState,
        debouncedFilterState,
        totalRecords,
        setTotalRecords,
        cleanSearchText
    } = useFilter({
        columns: columnsDefinition,
        debounceTime: debounceTime,
        rowsPerPage,
        rowsPerPageOptions,
        tableRef,
        extraFilter,
    });

    //const searchText = cleanSearchText(debouncedFilterState.search);
    //?type=Diretor
    const indexColumnType = columns.findIndex((c) => c.name === "type");
    const columnType = columns[indexColumnType];
    const typeFilterValue = filterState.extraFilter && (filterState.extraFilter.type as never);
    (columnType.options as any).filterList = typeFilterValue
        ? [typeFilterValue]
        : [];

    // const serverSideFilterList = columns.map((column) => []);
    // if (typeFilterValue) {
    //     serverSideFilterList[indexColumnType] = [typeFilterValue];
    // }

    async function getData() {

        try {
            const {data} = await castMemberHttp.list<ListResponse<CastMember>>({
                queryParams: {
                    search: cleanSearchText(filterState.search),
                    page: filterState.pagination.page,
                    per_page: filterState.pagination.per_page,
                    sort: filterState.order.sort,
                    dir: filterState.order.dir,
                    ...(debouncedFilterState.extraFilter &&
                        debouncedFilterState.extraFilter.type && {
                          type: invert(CastMemberTypeMap)[debouncedFilterState.extraFilter.type],
                    })
                }
            });
            if (subscribed.current) {
                setData(data.data);
                setTotalRecords(data.meta.total);
            }
        } catch (error) {
            console.error(error);

            if(castMemberHttp.isCancelledRequest(error)){
                return;
            }

            snackbar.enqueueSnackbar(
                'Error trying to list cast members',
                {variant:"error"}
            );
        }
    }

    useEffect(() => {
        subscribed.current = true;
        getData();
        return () => {
            subscribed.current = false;
        }
    }, [
        cleanSearchText(debouncedFilterState.search),
        debouncedFilterState.pagination.page,
        debouncedFilterState.pagination.per_page,
        debouncedFilterState.order,
        JSON.stringify(debouncedFilterState.extraFilter),
    ]);

    return (
        <MuiThemeProvider theme={makeActionStyles(columnsDefinition.length -1)}>
            <DefaultTable 
                title="Cast Member List"
                columns={columns} 
                data={data}
                loading={loading}
                debouncedSearchTime={debouncedSearchTime}
                ref={tableRef}
                options={{
                    serverSide: true,
                    searchText: filterState.search as any,
                    page: filterState.pagination.page - 1,
                    rowsPerPage: filterState.pagination.per_page,
                    rowsPerPageOptions,
                    count: totalRecords,
                    onFilterChange: (column, filterList, type) => {
                        const columnIndex = columns.findIndex((c) => c.name === column);
                        //[ [], [], ['Diretor'], [], []  ]
                        filterManager.changeExtraFilter({
                          [column as any]: filterList[columnIndex].length
                            ? filterList[columnIndex][0]
                            : null,
                        });
                      },
                    customToolbar: () => (
                        <FilterResetButton 
                            handleClick={ () => filterManager.resetFilter() }
                        />
                    ),
                    onSearchChange: (value) => filterManager.changeSearch(value),
                    onChangePage: (page) => filterManager.changePage(page),
                    onChangeRowsPerPage: (perPage) => filterManager.changeRowsPerPage(perPage),
                    onColumnSortChange: (changedColumn: string, direction: string) =>
                        filterManager.changeColumnSort(changedColumn, direction)
                }}
            />
        </MuiThemeProvider>
    );
}

export default Table;
