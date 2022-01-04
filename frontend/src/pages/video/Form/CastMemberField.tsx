import { FormControl, FormControlProps, FormHelperText, Typography } from '@material-ui/core';
import * as React from 'react';
import AsyncAutocomplete from '../../../components/AsyncAutocomplete';
import GridSelected from '../../../components/GridSelected';
import GridSelectedItem from '../../../components/GridSelectedItem';
import useCollectionManager from '../../../hooks/useCollectionManager';
import useHttpHandled from '../../../hooks/useHttpHandled';
import castMemberHttp from '../../../util/http/cast-member-http';

interface CastMemberFieldProps {
    castMembers: any[];
    setCastMembers: (castMembers) => void;
    error: any;
    disabled?: boolean;
    FormControlProps?: FormControlProps;
}

const CastMemberField: React.FC<CastMemberFieldProps> = (props) => {

    const autocompleteHttp = useHttpHandled();
    const { error, disabled, castMembers, setCastMembers} = props;
    const {addItem, removeItem} = useCollectionManager(castMembers, setCastMembers);

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

    return (
        <>
            <AsyncAutocomplete 
                fetchOptions={fetchOptions}    
                AutocompleteProps={{
                    clearOnEscape: true,
                    getOptionLabel: option => option.name,
                    getOptionSelected: (option, value) => option.id === value.id,
                    onChange: (e, v) => addItem(v),
                    disabled: disabled === true
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
}

export default CastMemberField;