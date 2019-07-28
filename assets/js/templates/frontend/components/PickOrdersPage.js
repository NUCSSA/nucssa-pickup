import React, { Component } from 'react';
import path from 'path';
import axios from 'axios';
import { userEndpoint, nonce, orderEndpoint } from '../../../utils/constants';
import moment from 'moment';
import { datetimeDisplayFormat } from '../../../utils/utils';

export default class DriverApplicationPage extends Component {
  constructor(props) {
    super(props);
    this.state = {
      orders: []
    };

    this.actionCellHTML = this.actionCellHTML.bind(this);
    this.goHome = this.goHome.bind(this);
  }

  componentDidMount() {
    this.fetchPendingOrders();
  }

  async fetchPendingOrders() {
    try {
      // get all pending orders
      const { data: { data: pendingOrders } } = await axios.get(`${orderEndpoint}&type=pending`);
      // console.log('pending orders', pendingOrders);
      this.setState({ orders: pendingOrders });
    } catch (error) {
    }
  }

  goHome() {
    this.props.history.push('/');
  }

  actionCellHTML(order) {
    const pickOrder = () => {
      axios.put(`${orderEndpoint}&driver_action=pick`, {order_id: order.id})
        .then(res => {
          if (res.data.success) {
            // 接单成功 重新获取所有订单
            M.toast({ html: '<p class="green-text center-align"><strong>成功接单</strong></p>' });
            setTimeout(() => this.fetchPendingOrders(), 700);
          }
        })
        .catch(err => {
          if (err.response.status == 410) {
            M.toast({ html: '<p class="red-text center-align"><strong>手慢了，其他司机接了这单。。。</strong></p>' });
            setTimeout(() => this.fetchPendingOrders(), 700);
          }
          console.log('Error', err.response.status);
        })
    }
    return (<>
      <a className="btn" onClick={pickOrder} style={{minWidth: '65px'}}>接单</a>
    </>);
  }

  render() {
    return (
      <main className="container">
        <div className="section row">
          <h5 className="center-align">活跃订单</h5>
          <table className="striped">
            <thead>
              <tr>
                <th>飞机到达时间</th>
                <th>随行人数</th>
                <th>行李数</th>
                <th>目的地址</th>
                <th>备注</th>
                <th>订单操作</th>
              </tr>
            </thead>
            <tbody>
              {
                this.state.orders.map(order => (
                  <tr key={order.id}>
                    <td>{moment(order.arrival_datetime).format(datetimeDisplayFormat)}</td>
                    <td>{order.companion_count}</td>
                    <td>{order.luggage_count}</td>
                    <td>{order.drop_off_address}</td>
                    <td>{order.note}</td>
                    <td>{this.actionCellHTML(order)}</td>
                  </tr>
                ))
              }
            </tbody>
          </table>
        </div>
        <div className="row center">
          <button className="btn-large red darken-2 waves-effect waves-light" onClick={this.goHome}>
            返回主页 <i className="material-icons left">home</i>
          </button>
        </div>
      </main>
    );
  }
}
