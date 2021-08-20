import * as React from 'react';
import {Chip, createMuiTheme, MuiThemeProvider} from "@material-ui/core";
import theme from "../theme";

const localTheme = createMuiTheme({
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