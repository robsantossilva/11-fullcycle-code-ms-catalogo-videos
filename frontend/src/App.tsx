import { Box, CssBaseline, MuiThemeProvider } from '@material-ui/core';
import { SnackbarProvider } from './components/SnackbarProvider';
import React from 'react';
import { BrowserRouter } from 'react-router-dom';
import './App.css';
import Breadcrumbs from './components/Breadcrumbs';
import { Navbar } from './components/Navbar';
import AppRouter from './routes/AppRouter';
import theme from './theme';

const App: React.FC = () => {
  return (
    <React.Fragment>
      <MuiThemeProvider theme={theme}>
        <SnackbarProvider >
          <CssBaseline/>
          <BrowserRouter>
            <Navbar />
            <Box paddingTop={'70px'}>
              <Breadcrumbs />
              <AppRouter />
            </Box>
          </BrowserRouter>
        </SnackbarProvider>          
      </MuiThemeProvider>    
    </React.Fragment>
  );
}

export default App;