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
        email: '',
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
      // console.log('user', user);
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

    const { wechat, phone, email } = this.state.user;
    const name = this.refName.current.value.trim();
    const carrier = this.refCarrier.current.value.trim();
    const password1 = this.refPassword1.current.value.trim();
    const password2 = this.refPassword2.current.value.trim();
    const plateNumber = this.refVehiclePlateNumber.current.value.trim();
    const makeModel = this.refVehicleMakeAndModel.current.value.trim();
    const vehicleColor = this.refVehicleColor.current.value.trim();

    let errors = [];
    const validateRequired = ([displayName, val]) => {
      if (!val) {
        errors.push(`${displayName} is required`);
      }
    };
    // Require wechat, email, name, password1 field.
    [['WeChat', wechat], ['Email', email], ['姓名', name], ['Password', password1]].forEach(validateRequired);
    if (password1 !== password2) {
      errors.push('Password Mismatch');
    }
    // Require phone and vehicle information if isDriver
    if (this.state.isDriver) {
      [['手机号', phone], ['手机运营商', carrier], ['车牌号', plateNumber], ['汽车型号', makeModel], ['汽车颜色', vehicleColor]].forEach(validateRequired);
    }

    if (errors.length > 0) {
      this.setState({
        message: <div className="card-panel red lighten-3 white-text center-align">
          {errors.map(
            (val, index) => <p key={index}>{val}</p>
          )}
        </div>
      });
      return;
    }

    const userData = {
      wechat, name, email, phone, carrier, password: password1
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
          const vehiclePlateNumber = this.refVehiclePlateNumber.current.value.trim();
          const vehicleMakeAndModel = this.refVehicleMakeAndModel.current.value.trim();
          const vehicleColor = this.refVehicleColor.current.value.trim();
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
                  <input type="text" id="edit-wechat" name="user[wechat]" className="validate" value={this.state.user.wechat} onChange={(e) => this.setState({user : {...this.state.user, wechat: e.currentTarget.value.trim()}})} required />
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
                <div className="input-field col s12">
                  <i className="material-icons prefix">email</i>
                  <input type="email" id="edit-email" name="user[email]" className="validate" value={this.state.user.email} onChange={e => this.setState({user: {...this.state.user, email: e.currentTarget.value.trim()}})} required />
                    <label htmlFor="edit-email">Email</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s6">
                  <i className="material-icons prefix">phone</i>
                  <input type="number" id="edit-phone" name="user[phone]" value={this.state.user.phone} onChange={e => this.setState({user: {...this.state.user, phone: e.currentTarget.value.trim()}})} required={this.state.isDriver} />
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
