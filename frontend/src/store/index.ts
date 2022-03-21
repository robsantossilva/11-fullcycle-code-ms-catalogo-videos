import { applyMiddleware, combineReducers, createStore } from "redux";
import createSagaMiddleware from "redux-saga";
import rootSaga from "./root-saga";
import upload from './upload';

const sagaMiddleware = createSagaMiddleware();

const reducers = combineReducers({
    upload
});

const store = createStore(
    reducers,
    applyMiddleware(sagaMiddleware)
)

sagaMiddleware.run(rootSaga)

export default store;