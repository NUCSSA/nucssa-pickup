import React, { Component } from 'react';
import path from 'path';
import axios from 'axios';
import { userEndpoint, nonce } from '../../../utils/constants';

export default class DriverApplicationPage extends Component {
  constructor() {
    super();
    this.state = {
      message: '',
      wechat: '',
      phone: '',
      isDriver: false,
    };

    this.submit = this.submit.bind(this);
    this.goHome = this.goHome.bind(this);

    this.refName = React.createRef();
    this.refCarrier = React.createRef();
    this.refPassword1 = React.createRef();
    this.refPassword2 = React.createRef();
  }

  async componentDidMount() {
    try {
      const resp = await axios.get(userEndpoint);
      const user = resp.data.data.user;
      this.setState({
        wechat: user.wechat,
        phone: user.phone,
        isDriver: user.isDriver,
      });
      this.refName.current.value = user.name;
      this.refCarrier.current.value = user.carrier;
    } catch (err) {
    }
  }

  goHome() {
    this.props.history.push('/');
  }

  submit(e) {
    e.preventDefault();

    const { wechat, phone } = this.state;
    const name = this.refName.current.value;
    const carrier = this.refCarrier.current.value;
    const password1 = this.refPassword1.current.value;
    const password2 = this.refPassword2.current.value;

    if (password1 !== password2) {
      this.setState({
        message: <div className="card-panel red lighten-3 white-text center-align">
          Password Mismatch!
        </div>
      });
      return;
    }

    const data = {
      wechat, name, phone, carrier, password: password1
    };

    axios.put(userEndpoint, data)
      .then(res => {
        // redirect back to home page
        this.props.history.push({
          pathname: '/',
          state: {
            autoDismissMessage: <div className="card-panel green white-text center-align">
              更新成功
            </div>
          }
        })
      })
      .catch(err => {
        // show error message
        this.setState({
          message: (
            <div className="card-panel red lighten-3 white-text center-align">
              <p>表格提交失败，稍后重试.</p>
              <p>如果一直不成功，直接发邮件联系我们<a href="mailto:pickup@nucssa.org">pickup@nucssa.org</a></p>
            </div>
          )
        });
        console.log('Error', err);
      });
  }

  render() {

    return (
      <main className="container">
        <div className="section row">
          <h5 className="center-align">更改个人信息</h5>
          <div className="col s12">
            <div id="message">{this.state.message}</div>
            <form action="" className="col s12" method="post" onSubmit={this.submit}>
              <div className="row">
                <div className="input-field col s12">
                  <i className="icon icon-wechat material-icons prefix"></i>
                  <input type="text" id="register-wechat" name="user[wechat]" className="validate" value={this.state.wechat} onChange={(e) => this.setState({wechat: e.currentTarget.value})} required />
                  <label htmlFor="register-wechat">WeChat</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s12">
                  <i className="material-icons prefix">account_circle</i>
                  <input ref={this.refName} type="text" id="register-name" name="user[name]" className="validate" required />
                    <label htmlFor="register-name">姓名</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s6">
                  <i className="material-icons prefix">phone</i>
                  <input type="tel" id="register-phone" name="user[phone]" value={this.state.phone} onChange={(e) => this.setState({phone: e.currentTarget.value})} required={this.state.isDriver} />
                  <label htmlFor="register-phone">手机号{!this.state.isDriver && '(Optional)'}</label>
                </div>
                <div className="input-field col s6">
                  <input ref={this.refCarrier} type="text" id="register-carrier" name="user[carrier]" required={this.state.isDriver} />
                  <label htmlFor="register-carrier">手机运营商{!this.state.isDriver && '(Optional)'}</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s6">
                  <i className="material-icons prefix">lock</i>
                  <input ref={this.refPassword1} type="password" id="register-password" name="user[password]" className="validate" required />
                  <label htmlFor="register-password">Password</label>
                </div>
                <div className="input-field col s6">
                  <input ref={this.refPassword2} type="password" id="register-password2" name="user[password2]" className="validate" required />
                  <label htmlFor="register-password2">Enter Password Again</label>
                </div>
              </div>
              <div className="row center">
                <button className="btn-large yellow darken-3 waves-effect waves-light" onClick={this.goHome}>
                  返回 <i className="material-icons left">arrow_back</i>
                </button>
                <button className="btn-large blue waves-effect waves-light" type="submit" style={{ marginLeft: '5px' }}>
                  提交 <i className="material-icons right">send</i>
                </button>
              </div>
            </form>
          </div>
        </div>
      </main >
    );
  }
}
