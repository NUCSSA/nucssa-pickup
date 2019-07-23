import React, { Component } from 'react';
import path from 'path';
import axios from 'axios';
import { orderEndpoint, nonce } from '../../../utils/constants';
import moment from 'moment';
import { datetimeSQLFormat } from '../../../utils/utils';

export default class CreateEditOrderPage extends Component {
  constructor(props) {
    super(props);

    this.refHuskyID = React.createRef();
    this.refAdmissioNotice = React.createRef();
    this.refAddress = React.createRef();
    this.refFlightNum = React.createRef();
    this.refArrivalDate = React.createRef();
    this.refArrivalTime = React.createRef();
    this.refTerminal = React.createRef();
    this.refNote = React.createRef();
    this.refMessage = React.createRef();
    this.refCompanionCount = React.createRef();
    this.refLuggageCount = React.createRef();
    this.refUrgentContact = React.createRef();

    this.submitHandler = this.submitHandler.bind(this);
    this.goHome = this.goHome.bind(this);

    if (this.props.location.state && this.props.location.state.order) {
      this.isEditing = true;
      this.order = this.props.location.state.order;
      console.log('order', this.order);
    } else {
      this.isEditing = false;
    }
  }

  componentDidMount() {
    const datepickerInstance = M.Datepicker.init(this.refArrivalDate.current, {
      defaultDate: this.isEditing ? new Date(this.order.arrival_datetime) : null,
      setDefaultDate: this.isEditing
    });
    const timepickerInstance = M.Timepicker.init(this.refArrivalTime.current, {
      defaultTime: this.isEditing ? moment(this.order.arrival_datetime).format('hh:mm A') : 'now',
    });
    const formSelectInstance = M.FormSelect.init(this.refTerminal.current);
    M.CharacterCounter.init(this.refNote.current);
    M.CharacterCounter.init(this.refUrgentContact.current);

    if(this.isEditing){
      timepickerInstance._updateTimeFromInput();
      timepickerInstance.done();
      this.refAddress.current.value = this.order.drop_off_address;
      this.refFlightNum.current.value = this.order.flight;
      this.refAddress.current.value = this.order.drop_off_address;
      this.refTerminal.current.value = this.order.arrival_terminal;
      this.refCompanionCount.current.value = this.order.companion_count;
      this.refLuggageCount.current.value = this.order.luggage_count;
      this.refUrgentContact.current.value = this.order.urgent_contact_info;
      this.refNote.current.value = this.order.note;
      this.refHuskyID.current.value = this.order.huskyID;
    }
  }

  submitHandler(e) {
    e.preventDefault();

    const address = this.refAddress.current.value;
    const flight = this.refFlightNum.current.value;
    const arrivalDate = this.refArrivalDate.current.value;
    const arrivalTime = this.refArrivalTime.current.value;
    const arrivalDatetime = moment(`${arrivalDate} ${arrivalTime}`, 'MMM DD, YYYY hh:mm A').format(datetimeSQLFormat);
    const terminal = this.refTerminal.current.value;
    const note = this.refNote.current.value;
    const companionCount = this.refCompanionCount.current.value;
    const luggageCount = this.refLuggageCount.current.value;
    const urgentContactInfo = this.refUrgentContact.current.value;
    const huskyID = this.refHuskyID.current.value;
    const term = 'Fall 2019';
    // console.log('arrival Time', arrivalDatetime);
    // return;

    let data, method;
    if (!this.isEditing){ // Creating a new order, posting file uploads
      data = new FormData();
      const fileAdmissioNotice = this.refAdmissioNotice.current.files[0];
      data.append('address', address);
      data.append('flight', flight);
      data.append('arrivalDatetime', arrivalDatetime);
      data.append('terminal', terminal);
      data.append('companionCount', companionCount);
      data.append('luggageCount', luggageCount);
      data.append('urgentContactInfo', urgentContactInfo);
      data.append('note', note);
      data.append('term', term);
      data.append('huskyID', huskyID);
      data.append('admissionNotice', fileAdmissioNotice, 'admission_notice' + path.extname(fileAdmissioNotice.name));

      method = 'post';

    } else { // Updating an existing order, no need for file uploads, use normal PUT Request
      data = {
        address, flight, arrivalDatetime, terminal, companionCount, luggageCount, urgentContactInfo,
        note, term, huskyID,
        id: this.order.id,
        passenger: this.order.passenger.id,
      };

      method = 'put';
    }

    const config = {
      headers: {
        'Content-Type': this.isEditing ? 'application/json' : 'multipart/form-data',
        'X-WP-Nonce': nonce
      }
    };

    axios[method](orderEndpoint, data, config)
      .then(res => {
        this.props.history.push('/');
        // this.props.history.push({
        //   pathname: '/',
        //   state: {
        //     autoDismissMessage: <div className="card-panel green white-text center-align">
        //       {this.isEditing ? '订单更新成功' : '您的接机请求提交成功! 请耐心等待司机接单！我们会以邮件通知您。'}
        //     </div>
        //   }
        // });
      })
      .catch(err => {
        // show error message
        this.refMessage.current.innerHTML = `<div class="card-panel red lighten-3 white-text center-align">
          <p>提交失败，稍后重试.</p>
          <p>如果一直不成功，直接发邮件联系我们 <a href="mailto:pickup@nucssa.org">pickup@nucssa.org</a></p>
        </div>`;

        console.log('Error', err);
      })

  };

  goHome(){
    this.props.history.push('/');
  }

  render () {

    return (
      <main className="container">
        <div className="section row">
          <h5 className="center-align">{this.isEditing ? '修改订单' : '创建订单'}</h5>
          <div className="description card red lighten-1 center-align">
            <div className="card-content white-text">
              <span className="card-title">请注意:</span>
              <p>我们的接机服务仅为新生提供，需要提交录取通知书作为新生证明!</p>
            </div>
          </div>
          <div className="col s12">
            <div ref={this.refMessage} id="message"></div>
            <form action="" className="col s12" method="post" onSubmit={this.submitHandler}>
              <div className="row">
                <div className="input-field col s12">
                  <i className="material-icons prefix">ID</i>
                  <input ref={this.refHuskyID} type="number" id="huskyID" className="validate" required />
                  <label htmlFor="huskyID">Husky ID(NUID)</label>
                </div>
              </div>
              {
                !this.isEditing &&
                (
                  <div className="row">
                    <div className="input-field file-field col s12">
                      <div className="btn">
                        <span>录取通知书</span>
                        <input type="file" ref={this.refAdmissioNotice} required accept=".jpg,.jpeg,.png" />
                      </div>
                      <div className="file-path-wrapper">
                        <input className="file-path validate" type="text" placeholder="支持JPG, JPEG, PNG" />
                      </div>
                    </div>
                  </div>
                )
              }
              <div className="row">
                <div className="input-field col s12">
                  <i className="material-icons prefix">add_location</i>
                  <input ref={this.refAddress} type="text" id="address" className="validate" required />
                  <label htmlFor="address">目的地址</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s12">
                  <i className="material-icons prefix icon icon-paper-plane"></i>
                  <input ref={this.refFlightNum} type="text" id="flight-number" className="validate" required style={{textTransform: 'uppercase'}} />
                  <label htmlFor="flight-number">航班号码</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s7">
                  <i className="material-icons prefix">access_time</i>
                  <input ref={this.refArrivalDate} type="text" id="arrival-date" className="datepicker validate" required />
                  <label htmlFor="arrival-date">到达日期(Boston)</label>
                </div>
                <div className="input-field col s5">
                  <input ref={this.refArrivalTime} type="text" id="arrival-time" className="timepicker validate" required />
                  <label htmlFor="arrival-time">时间(Boston)</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s12">
                  <i className="material-icons prefix">flag</i>
                  <select ref={this.refTerminal} className="validate" defaultValue="" required>
                    <option value="" disabled>Choose your option</option>
                    <option value="A">Terminal A</option>
                    <option value="B">Terminal B</option>
                    <option value="C">Terminal C</option>
                    <option value="D">Terminal D</option>
                    <option value="E">Terminal E</option>
                  </select>
                  <label>到达航站楼(Arrival Terminal)</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s12">
                  <i className="material-icons prefix">people</i>
                  <input ref={this.refCompanionCount} type="number" id="companion-count" className="validate" defaultValue="0" required />
                  <label htmlFor="companion-count">随行家属人数</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s12">
                  <i className="material-icons prefix icon icon-suitcase-with-wheels"></i>
                  <input ref={this.refLuggageCount} type="number" id="luggage-count" className="validate" defaultValue="0" required />
                  <label htmlFor="luggage-count">行李数量</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s12">
                  <i className="material-icons prefix">contact_phone</i>
                  <textarea ref={this.refUrgentContact} id="urgent-contact" className="materialize-textarea" data-length="200" required></textarea>
                  <label htmlFor="urgent-contact">紧急联系人信息</label>
                </div>
              </div>
              <div className="row">
                <div className="input-field col s12">
                  <i className="material-icons prefix">message</i>
                  <textarea ref={this.refNote} id="note" className="materialize-textarea" data-length="500"></textarea>
                  <label htmlFor="note">备注信息(给司机)</label>
                </div>
              </div>
              <div className="row center">
                <button className="btn-large yellow darken-3 waves-effect waves-light" type="button" onClick={this.goHome}>
                  返回 <i className="material-icons left">arrow_back</i>
                </button>
                <button className="btn-large blue waves-effect waves-light" type="submit" style={{ marginLeft: '5px' }}>
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