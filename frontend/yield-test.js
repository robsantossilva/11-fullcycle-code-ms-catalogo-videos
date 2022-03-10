const { default: axios } = require("axios");

function* test()
{
    yield "react";
    console.log("Robson");
    yield "saga";
    yield axios.get();
    yield* test1();

}

function* test1(){
    yield "teste1";
    yield "teste2";
}

const iterator = test();

console.log(iterator.next());
console.log(iterator.next());
console.log(iterator.next());