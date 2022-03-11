import { combineReducers, createStore } from "redux";

import upload from './upload';

const reducers = combineReducers({
    upload
});

const store = createStore(reducers)

export default store;