import * as React from 'react';
import { FormControl, FormControlProps, FormHelperText, Typography } from '@material-ui/core';
import { MutableRefObject, RefAttributes } from 'react';
import AsyncAutocomplete, { AsyncAutocompleteComponent } from '../../../components/AsyncAutocomplete';
import GridSelected from '../../../components/GridSelected';
import GridSelectedItem from '../../../components/GridSelectedItem';
import useCollectionManager from '../../../hooks/useCollectionManager';
import useHttpHandled from '../../../hooks/useHttpHandled';
import castMemberHttp from '../../../util/http/cast-member-http';
import { useImperativeHandle } from 'react';
import { useRef } from 'react';

interface CastMemberFieldProps extends RefAttributes<CastMemberFieldComponent> {
    castMembers: any[];
    setCastMembers: (castMembers) => void;
    error: any;
    disabled?: boolean;
    FormControlProps?: FormControlProps;
}

export interface CastMemberFieldComponent {
    clear: () => void
}

const CastMemberField = React.forwardRef<CastMemberFieldComponent, CastMemberFieldProps>((props, ref) => {

    const autocompleteHttp = useHttpHandled();
    const { error, disabled, castMembers, setCastMembers} = props;
    const {addItem, removeItem} = useCollectionManager(castMembers, setCastMembers);
    const autocompleteRef = useRef() as MutableRefObject<AsyncAutocompleteComponent>

    function fetchOptions(searchText) {
        return autocompleteHttp(
            castMemberHttp
                .list({
                    queryParams: { 
                        all: ""
                    }
                })
        )
        .then((data) => {
            return data.data
        })
        .catch(error => console.log(error));
    }

    useImperativeHandle(ref, ()=>({
        clear: ()=> autocompleteRef.current.clear()
    }));

    return (
        <>
            <AsyncAutocomplete 
                ref={autocompleteRef}
                fetchOptions={fetchOptions}    
                AutocompleteProps={{
                    clearOnEscape: true,
                    getOptionLabel: option => option.name,
                    getOptionSelected: (option, value) => option.id === value.id,
                    onChange: (e, v) => addItem(v),
                    disabled: disabled === true,
                }}
                TextFieldProps={{
                    label: 'Elenco',
                    error: error !== undefined
                }}      
            />
            <FormControl
                fullWidth
                margin={'normal'}
                error={error !== undefined}
                disabled={disabled === true}
                {...props.FormControlProps}
            >
                <GridSelected>
                {
                        castMembers.map( (castMember, key) => {
                            return (
                                <GridSelectedItem 
                                    key={key} 
                                    onDelete={ 
                                        () => removeItem(castMember) 
                                    } 
                                    xs={12}
                                    md={6}
                                >
                                    <Typography 
                                        noWrap={true}
                                    >{castMember.name}</Typography>
                                </GridSelectedItem>
                            )
                        
                        })
                    }
                </GridSelected>
                {
                    (error && castMembers.length) ? <FormHelperText>{error.message}</FormHelperText> : null
                }
            </FormControl>
        </>
    );
});

export default CastMemberField;