import * as Typings from "./types";
import {createActions, createReducer} from 'reduxsauce';
import { AddUploadAction } from "./types";

// Criando Action Types e Creators
export const {Types, Creators} = createActions<{
    ADD_UPLOAD: string
}, {
    addUpload(payload: Typings.AddUploadAction['payload']): Typings.AddUploadAction
}>
({
    setSearch: ['payload'],
    setPage: ['payload'],
    setPerPage: ['payload'],
    setOrder: ['payload'],
    setReset: ['payload'],
    updateExtraFilter: ['payload'],
});

// Definindo State Inicial
export const INITIAL_STATE: Typings.State = {
    uploads: []
};

// Criando Reducer apartir do State Inicial e as Actions
const reducer = createReducer<Typings.State, Typings.Actions>(INITIAL_STATE, {
    [Types.ADD_UPLOAD]: addUpload as any
});

export default reducer;

function addUpload(state = INITIAL_STATE, action: Typings.AddUploadAction): Typings.State {
    return {
        uploads: []
    };
}