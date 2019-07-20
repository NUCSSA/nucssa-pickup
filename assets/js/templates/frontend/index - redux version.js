import React from 'react';
import { render } from 'react-dom';
import { HashRouter as Router, Route, Switch, NavLink } from 'react-router-dom';
import { Provider, connect } from 'react-redux';
import PickupPage from './components/PickupPage';
import DriverApplicationPage from './components/DriverApplicationPage';
import EditUserInfoPage from './components/EditUserInfoPage';
import store from '../../redux/store';

export default (root) => {
  render(
    <Provider store={store} >
      <Index />
    </Provider>,
    root
  );
}

const Index = connect(state => state)( props => (
  <Router hashType="noslash">
    <Switch>
      <Route path="/" exact component={PickupPage} />
      <Route path="/driver-application" exact component={DriverApplicationPage} />
      <Route path="/edit-user-info" exact component={EditUserInfoPage} />
    </Switch>
  </Router>
));