import React, { Component } from 'react';
import path from 'path';
import axios from 'axios';
import { userEndpoint, driverEndpoint, nonce, pickupAdminEmail } from '../../../utils/constants';

export default class DriverApplicationPage extends Component {
  constructor() {
    super();
    this.state = {
      message: '',
      user: {
        wechat: '',
        phone: '',
        role: '',
      },
      isDriver: false,
    };

    this.submit = this.submit.bind(this);
    this.goHome = this.goHome.bind(this);

    this.refName = React.createRef();
    this.refCarrier = React.createRef();
    this.refPassword1 = React.createRef();
    this.refPassword2 = React.createRef();
    this.refVehiclePlateNumber = React.createRef();
    this.refVehicleMakeAndModel = React.createRef();
    this.refVehicleColor = React.createRef();
  }

  async componentDidMount() {
    try {
      let resp = await axios.get(userEndpoint);
      const user = resp.data.data.user;
      const isDriver = user.role === 'DRIVER' || user.role === 'PENDING_DRIVER';
      console.log('user', user);
      this.setState({user, isDriver});

      if (isDriver) {
        resp = await axios.get(driverEndpoint);
        const driver = resp.data.data.driver;
        this.refVehiclePlateNumber.current.value = driver.vehicle_plate_number;
        this.refVehicleMakeAndModel.current.value = driver.vehicle_make_and_model;
        this.refVehicleColor.current.value = driver.vehicle_color;
      }

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

    const { wechat, phone } = this.state.user;
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

    const userData = {
      wechat, name, phone, carrier, password: password1
    };

    const errHandler = err => {
      // show error message
      this.setState({
        message: (
          <div className="card-panel red lighten-3 white-text center-align">
            <p>表格提交失败，稍后重试.</p>
            <p>如果一直不成功，直接发邮件联系我们 <a href={`mailto:${pickupAdminEmail}`}>{pickupAdminEmail}</a></p>
          </div>
        )
      });
      console.log('Error', err);
    };
    const successHandler = () => {
      // redirect back to home page
      this.props.history.push({
        pathname: '/',
        state: {
          autoDismissMessage: <div className="card-panel green white-text center-align">
            更新成功
            </div>
        }
      });
    };

    axios.put(userEndpoint, userData)
      .then(res => {
        if (this.state.isDriver) {
          const vehiclePlateNumber = this.refVehiclePlateNumber.current.value;
          const vehicleMakeAndModel = this.refVehicleMakeAndModel.current.value;
          const vehicleColor = this.refVehicleColor.current.value;
          const driverData = {
            vehiclePlateNumber, vehicleMakeAndModel, vehicleColor
          };

          axios.put(driverEndpoint, driverData)
            .then(res => {
              successHandler();
            })
            .catch(errHandler);
        }
        successHandler();
      })
      .catch(errHandler);
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
                  <input type="text" id="edit-wechat" name="user[wechat]" className="validate" value={this.state.user.wechat} onChange={(e) => this.setState({user : {...this.user, wechat: e.currentTarget.value}})} required />
                  <label htmlFor="edit-wechat">WeChat</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s12">
                  <i className="material-icons prefix">account_circle</i>
                  <input ref={this.refName} type="text" id="edit-name" name="user[name]" className="validate" required />
                    <label htmlFor="edit-name">姓名</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s6">
                  <i className="material-icons prefix">phone</i>
                  <input type="number" id="edit-phone" name="user[phone]" value={this.state.user.phone} onChange={e => this.setState({user: {...this.state.user, phone: e.currentTarget.value}})} required={this.state.isDriver} />
                  <label htmlFor="edit-phone">手机号{!this.state.isDriver && '(Optional)'}</label>
                </div>
                <div className="input-field col s6">
                  <input ref={this.refCarrier} type="text" id="edit-carrier" name="user[carrier]" required={this.state.isDriver} />
                  <label htmlFor="edit-carrier">手机运营商{!this.state.isDriver && '(Optional)'}</label>
                </div>
              </div>
              {
                (this.state.isDriver) &&
                (
                  <div className="row">
                    <div className="input-field col s4">
                      <i className="material-icons prefix">directions_car</i>
                      <input ref={this.refVehiclePlateNumber} type="text" id="edit-vehicle-plate-number" style={{textTransform: 'uppercase'}} required />
                      <label htmlFor="edit-vehicle-plate-number">车牌号</label>
                    </div>
                    <div className="input-field col s4">
                      <input ref={this.refVehicleMakeAndModel} type="text" id="edit-vehicle-make-and-model" required />
                      <label htmlFor="edit-vehicle-make-and-model">汽车型号</label>
                    </div>
                    <div className="input-field col s4">
                      <input ref={this.refVehicleColor} type="text" id="edit-vehicle-color" required />
                      <label htmlFor="edit-vehicle-color">汽车颜色</label>
                    </div>
                  </div>
                )
              }

              <div className="row">
                <div className="input-field col s6">
                  <i className="material-icons prefix">lock</i>
                  <input ref={this.refPassword1} type="password" id="edit-password" name="user[password]" className="validate" required />
                  <label htmlFor="edit-password">Password</label>
                </div>
                <div className="input-field col s6">
                  <input ref={this.refPassword2} type="password" id="edit-password2" name="user[password2]" className="validate" required />
                  <label htmlFor="edit-password2">Enter Password Again</label>
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
