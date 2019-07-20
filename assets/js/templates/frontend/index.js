import React from 'react';
import { render } from 'react-dom';
import { HashRouter as Router, Route, Switch, NavLink } from 'react-router-dom';
import HomePage from './components/HomePage';
import DriverApplicationPage from './components/DriverApplicationPage';
import EditUserInfoPage from './components/EditUserInfoPage';
import CreateEditOrderPage from './components/CreateEditOrderPage';
import PickOrdersPage from './components/PickOrdersPage';

export default (root) => {
  render(<Index />, root);
}

const Index = props => (
  <Router hashType="noslash">
    <Switch>
      <Route path="/" exact component={HomePage} />
      <Route path="/driver-application" exact component={DriverApplicationPage} />
      <Route path="/edit-user-info" exact component={EditUserInfoPage} />
      <Route path="/new-order" exact component={CreateEditOrderPage} />
      <Route path="/edit-order" exact component={CreateEditOrderPage} />
      <Route path="/pick-orders" exact component={PickOrdersPage} />
    </Switch>
  </Router>
);
