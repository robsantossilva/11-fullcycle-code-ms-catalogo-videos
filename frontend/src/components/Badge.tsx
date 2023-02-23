import * as React from 'react';
import {Chip, MuiThemeProvider, createTheme} from "@material-ui/core";
import theme from "../theme";

const localTheme = createTheme({
    palette: {
        primary: theme.palette.success,
        secondary: theme.palette.error
    }
});

export const BadgeYes = ({label}) => {
    return (
        <MuiThemeProvider theme={localTheme}>
            <Chip label={label} color="primary"/>
        </MuiThemeProvider>
    );
};

export const BadgeNo = ({label}) => {
    return (
        <MuiThemeProvider theme={localTheme}>
            <Chip label={label} color="secondary"/>
        </MuiThemeProvider>
    );
};