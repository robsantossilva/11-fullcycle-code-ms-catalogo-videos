const yup = require('yup');

const schema = yup.object().shape({
    name: yup
        .string()
        .required()
});

schema
    .isValid({name:'ewwrwr'})
    .then(isValid => console.log(isValid));