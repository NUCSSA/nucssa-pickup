import React, { Component } from 'react';
import path from 'path';
import axios from 'axios';
import { userEndpoint, driverEndpoint, nonce } from '../../../utils/constants';

export default class DriverApplicationPage extends Component {
  constructor(props) {
    super(props);
    this.state = {
      message: '',
      phone: '',
      carrier: '',
      huskyID: '',
      vehiclePlateNumber: '',
      vehicleMakeAndModel: '',
      vehicleColor: '',
      term: 'Fall 2019',
    };

    this.submit = this.submit.bind(this);
    this.goHome = this.goHome.bind(this);

    this.refHuskyCard = React.createRef();
    this.refLicense = React.createRef();
  }

  async componentDidMount() {
    try {
      const { data: { data: {user} } } = await axios.get(userEndpoint);
      this.setState({
        phone: user.phone && user.phone || '',
        carrier: user.carrier && user.carrier || '',
      });
    } catch (err) {
    }
  }

  goHome(){
    this.props.history.push('/');
  }

  submit(e) {
    e.preventDefault();

    /**
     * get the following info and submit
     *  No validation required here, since it's already passed validations by now via form validation itself.
     * - phone
     * - carrier
     * - husky id
     * - husky card photo
     * - driver's license photo
     * - term
     *
     * REF: upload file with axios : https://github.com/axios/axios/issues/710
     */
    const { phone, carrier, huskyID, vehiclePlateNumber, vehicleMakeAndModel, vehicleColor, term} = this.state;
    const fileHuskyCard = this.refHuskyCard.current.files[0];
    const fileLicense = this.refLicense.current.files[0];

    let formData = new FormData();
    formData.append('phone', phone);
    formData.append('carrier', carrier);
    formData.append('huskyID', huskyID);
    formData.append('vehiclePlateNumber', vehiclePlateNumber);
    formData.append('vehicleMakeAndModel', vehicleMakeAndModel);
    formData.append('vehicleColor', vehicleColor);
    formData.append('term', term);
    formData.append('huskycard', fileHuskyCard, 'huskycard' + path.extname(fileHuskyCard.name));
    formData.append('license', fileLicense, 'license' + path.extname(fileLicense.name));
    const config = {
      headers: {
        'Content-Type': 'multipart/form-data',
        'X-WP-Nonce': nonce
      }
    };

    axios.post(driverEndpoint, formData, config)
      .then(res => {
        // redirect back to home page
        this.goHome();
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
          <h5 className="center-align">我要报名司机</h5>
          <div className="col s12">
            <div id="message">{this.state.message}</div>
            <form action="" className="col s12" method="post" onSubmit={this.submit} encType="multipart/form-data">
              <div className="row">
                <div className="input-field col s12">
                  <input type="number" className="validate" id="app-phone" value={this.state.phone} onChange={e => {this.setState({phone: e.currentTarget.value})}} required />
                  <label htmlFor="app-phone">电话</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s12">
                  <input type="text" className="validate" id="app-carrier" value={this.state.carrier} onChange={e => {this.setState({carrier: e.currentTarget.value})}} required />
                  <label htmlFor="app-carrier">手机运营商</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s12">
                  <input type="number" className="validate" id="app-huskyID" onChange={e => { this.setState({ huskyID: e.currentTarget.value }) }} required />
                  <label htmlFor="app-huskyID">Husky ID</label>
                </div>
              </div>
              <div className="row">
                <div className="file-field input-field">
                  <div className="btn">
                    <span>Husky Card</span>
                    <input type="file" ref={this.refHuskyCard} required accept=".jpg,.jpeg,.png" />
                  </div>
                  <div className="file-path-wrapper">
                    <input className="file-path validate" type="text" placeholder="请上传Husky Card的照片,支持JPG, JPEG, PNG" />
                  </div>
                </div>
              </div>
              <div className="row">
                <div className="file-field input-field">
                  <div className="btn">
                    <span>Driver's License</span>
                    <input type="file" ref={this.refLicense} required accept=".jpg,.jpeg,.png" />
                  </div>
                  <div className="file-path-wrapper">
                    <input className="file-path validate" type="text" placeholder="请上传驾照照片,支持JPG, JPEG, PNG" />
                  </div>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s12">
                  <input type="text" className="validate" id="app-vehicle-plate-number" value={this.state.vehiclePlateNumber} onChange={e => { this.setState({ vehiclePlateNumber: e.currentTarget.value }) }} style={{textTransform: 'uppercase'}} required />
                  <label htmlFor="app-vehicle-plate-number">车牌号</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s12">
                  <input type="text" className="validate" id="app-vehicle-make-and-model" value={this.state.vehicleMakeAndModel} onChange={e => { this.setState({ vehicleMakeAndModel: e.currentTarget.value }) }} required />
                  <label htmlFor="app-vehicle-make-and-model">汽车型号</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s12">
                  <input type="text" className="validate" id="app-vehicle-color" value={this.state.vehicleColor} onChange={e => { this.setState({ vehicleColor: e.currentTarget.value }) }} required />
                  <label htmlFor="app-vehicle-color">汽车颜色</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s12">
                  <input disabled type="text" className="validate" id="app-term" value={this.state.term} />
                </div>
              </div>
              <div className="row center">
                <button className="btn-large yellow darken-3 waves-effect waves-light" onClick={this.goHome}>
                  返回 <i className="material-icons left">arrow_back</i>
                </button>
                <button className="btn-large blue waves-effect waves-light" type="submit" style={{marginLeft: '5px'}}>
                  提交 <i className="material-icons right">send</i>
                </button>
              </div>
            </form>
          </div>
        </div>
      </main>
    );
  }
}
