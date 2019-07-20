import { createStore, combineReducers, applyMiddleware } from 'redux';
import { composeWithDevTools } from 'redux-devtools-extension/developmentOnly';
import thunk from 'redux-thunk';

export default createStore(
  combineReducers({

  }),
  composeWithDevTools(applyMiddleware(thunk))
);
