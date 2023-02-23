const {createStore, applyMiddleware} = require('redux');
const {default: createSagaMiddleware} = require('redux-saga');
const {take, put, call, actionChannel, debounce, select, all, fork} = require('redux-saga/effects');
const axios = require('axios');

function reducer(state = {value: 1}, action) {
    if (action.type === 'acaoY') {
        return {...state, text: action.value}
    }
    if (action.type === 'acaoX') {
        return {value: action.value};
    }

    return state;
}

function* sagaNonBlocking(){
    console.log('3 - antes do call');
    const {data} = yield call(  //(search) => axios.get('http://nginx/api/videos?search='+search),search
        axios.get, 'http://localhost:8000/api/videos'
    );
    console.log('4 - depois do call');
    //yield put()
}

function* searchData(action) { //type, value
    //console.log('Hello World');
    //const channel = yield actionChannel('acaoY');
    //console.log(channel);
    //while (true) {
    console.log("1 - " + (yield select((state) => state.text)));
    console.log('2 - antes da acao Y');
    //const action = yield take(channel); //todas as action types
    const search = action.value;
    try {


        yield fork(sagaNonBlocking);
        console.log('5 - depois do fork');
        // const [response1, response2] = yield all([
        //     call(  //(search) => axios.get('http://nginx/api/videos?search='+search),search
        //         axios.get, 'http://nginx/api/videos?search=' + search
        //     ),
        //     call(
        //         //(search) => axios.get('http://nginx/api/videos?search='+search),search
        //         axios.get, 'http://nginx/api/categories?search=' + search
        //     )
        // ]);
        //console.log(response1.data.data.length, response2.data.data.length);
        // const {data} = yield call(
        //     //(search) => axios.get('http://nginx/api/videos?search='+search),search
        //     axios.get, 'http://nginx/api/videos?search=' + search
        // );
        //
        // const {data1} = yield call(
        //     //(search) => axios.get('http://nginx/api/videos?search='+search),search
        //     axios.get, 'http://nginx/api/categories?search=' + search
        // );
        console.log("6 - " + search);
        //console.log(data);
        yield put({
            type: 'acaoX',
            value: ''
        });
    } catch (e) {
        console.log(e);
        yield put({
            type: 'acaoX',
            error: e
        });
    }
    //}

    //proxima
    //console.log(result);

    //PEGAR OS DADOS DE ACTION TYPE
    //FAZER UMA REQUISIÇÃO AJAX
    //ATUALIZAR MEU STATE
}

function* helloWorld(){
    console.log(">> Inicio helloWorld()");

    //const actionType = 'acaoY';
    const actionType = yield actionChannel('acaoY'); // Garantir a sequencia dos dispatchs

    while(true){// Execução Ciclica com while(true)

        console.log('\n     >>> Hello World');

        // { type: 'acaoY', value: 'l' }
        const action = yield take(actionType);
        console.log("1 - ",action);

        const {data} = yield call(  //(search) => axios.get('http://nginx/api/videos?search='+search),search
        axios.get, 'http://localhost:8000/api/videos'
        );
        console.log('data: ', data.data.length);

        // { type: 'acaoY', value: 'lui' }
        const action2 = yield take(actionType);
        console.log("2 - ",action2);

        // { type: 'acaoY', value: 'luiz' }
        const action3 = yield take(actionType);
        console.log("3 - ",action3);

        // { type: 'acaoY', value: 'luiz c' }
        const action4 = yield take(actionType);
        console.log("4 - ",action4);

        // { type: 'acaoY', value: 'luiz ca' }
        const action5 = yield take(actionType);
        console.log("5 - ",action5);
        // put é executado na sequencia sem a necessidade de uma action
        const result = yield put({
            type: 'acaoY',
            value: 'Chamou PUT'
        });
        console.log("Put - ",result);
    }
}

function* debounceSearch() {
    yield debounce(1000, 'acaoY', searchData)
}

function* rootSaga(){
    yield all([
        //helloWorld(),
        debounceSearch()
    ])

    // yield fork(helloWorld);
    // yield fork(debounceSearch);
    //
    // console.log('final');
    //outras coisas
}

// const generator = helloWorldSaga();
// generator.next()
const sagaMiddleware = createSagaMiddleware();
const store = createStore(
    reducer,
    applyMiddleware(sagaMiddleware)
);
sagaMiddleware.run(rootSaga);

const action = (type, value) => store.dispatch({type, value});

// A Cada Action disparada um yield é acionado em helloWorld
action('acaoY', 'l');//esperar mudar o state
action('acaoY', 'lui');
action('acaoY', 'luiz');
action('acaoY', 'luiz c');
action('acaoY', 'luiz ca');
action('acaoY', 'Robson'); // Passa por todos os yields atualizando o state direto
//action('acaoW', 'a');
//o state ainda mudou
console.log("\nState: ",store.getState());