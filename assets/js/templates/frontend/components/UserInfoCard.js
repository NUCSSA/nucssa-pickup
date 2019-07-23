import React, { Component } from 'react';
import { pluginDirURL } from '../../../utils/constants';

export default class UserInfoCard extends Component {
  constructor(props){
    super(props);

    this.refTooltip = React.createRef();
  }

  componentDidMount() {
    M.Tooltip.init(this.refTooltip.current, {html: true});
  }

  driverCard() {
    // only show vehicle info row only when it exists.
    const vehicleInfo = this.props.vehiclePlateNumber &&
      `<tr>
        <th><i class="material-icons">directions_car</i></th>
        <td>${this.props.vehiclePlateNumber} | ${this.props.vehicleColor} | ${this.props.vehicleMakeAndModel}</td>
      </tr>`;
    return `
      <div class="card-content">
        <table>
          <tr>
            <th><i class="material-icons">account_circle</i></th>
            <td>${this.props.name}</td>
          </tr>
          <tr>
            <th><i class="material-icons icon icon-wechat"/></th>
            <td>${this.props.wechat}</td>
          </tr>
          <tr>
            <th><i class="material-icons">email</i></th>
            <td>${this.props.email}</td>
          </tr>
          <tr>
            <th><i class="material-icons">phone</i></th>
            <td>${this.props.phone}</td>
          </tr>
          ${vehicleInfo}
        </table>
      </div>
    `;
  }

  render() {

    return (
      <div
        ref={this.refTooltip}
        className="chip tooltipped"
        data-position="right"
        data-tooltip={this.driverCard()}
      >
        <img src={`${pluginDirURL}/public/images/driver-avatar.png`} alt="接机司机"/>
        { this.props.name }
      </div>
    );
  }
}
