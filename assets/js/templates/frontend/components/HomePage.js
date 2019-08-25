import React, { Component } from 'react';
import api from '../../../utils/api';
import UserInfoCard from './UserInfoCard';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { userEndpoint, orderEndpoint } from '../../../utils/constants';
import moment from 'moment';
import { datetimeDisplayFormat } from '../../../utils/utils';
import ConfirmationModal from './ConfirmationModal';

export default class HomePage extends Component {
  constructor() {
    super();
    this.state = {
      ownOrders: [],
      user: {role: 'PASSENGER'},
      managedOrders: [], // when this user is a driver
      hasOrderWaitingForApproval: false,
      orderToDrop: null, // drop action from driver
      orderToDelete: null, // delete action from passenger
    };

    this.refPageActionsFAB = React.createRef();

    this.ownOrdersSection = this.ownOrdersSection.bind(this);
    this.dropOrder = this.dropOrder.bind(this);
    this.deleteOrder = this.deleteOrder.bind(this);
    this.confirmDropOrderModalText = this.confirmDropOrderModalText.bind(this);
    this.confirmDeleteOrderModalText = this.confirmDeleteOrderModalText.bind(this);
  }

  async componentDidMount() {
    try {
      /**
       * Fetch Data
       * - role
       * - orders of driver
       * - user's own order
       */
      let resp = await axios.get(userEndpoint);
      const user = resp.data.data.user;
      // console.log('user', user);
      this.setState({user})

      resp = await axios.get(`${orderEndpoint}&user_role=passenger`);
      const ownOrders = resp.data.data;
      // console.log('ownOrders', ownOrders);
      const hasOrderWaitingForApproval = ownOrders.some(order => {
        // console.log('order', order);
        return order.approved === null;
      });
      this.setState({ ownOrders, hasOrderWaitingForApproval });

      if (user.role == 'DRIVER') {
        resp = await axios.get(`${orderEndpoint}&user_role=driver`);
        const managedOrders = resp.data.data;
        // console.log('managed orders', managedOrders);
        this.setState({managedOrders});
      }

    } catch (error) {

    }

    // init Confirmaiton Modals
    M.Modal.init(document.querySelectorAll('.modal'));
  }

  componentDidUpdate() {
    // init tooltips
    M.Tooltip.init(document.querySelectorAll('.tooltipped'));
    // init Floating Action Button
    M.FloatingActionButton.init(this.refPageActionsFAB.current);
  }

  dropOrder() {
    axios.put(`${orderEndpoint}&driver_action=drop`, { order_id: this.state.orderToDrop.id })
    .then(res => {
      this.setState({ managedOrders: this.state.managedOrders.filter(o => o.id !== this.state.orderToDrop.id) });
    });
  }

  confirmDropOrderModalText() {
    if (!this.state.orderToDrop) return null;

    const { passenger: { name: passengerName}, drop_off_address} = this.state.orderToDrop;
    let style = {
      passengerName: {
        color: 'dodgerblue', fontWeight: 'bold',
        textDecoration: 'underline',
      },
      address: {
        color: 'dodgerblue', fontWeight: 'bold',
        textDecoration: 'underline',
      }
    };
    return (
      <>
        <p>You are about to drop the order of <span style={style.passengerName}>{passengerName}</span></p>
        <p>with address <span style={style.address}>{drop_off_address}</span></p>
      </>
    );
  }

  confirmDeleteOrderModalText() {
    if (!this.state.orderToDelete) return null;

    const { drop_off_address } = this.state.orderToDelete;
    let style = {
      address: {
        color: 'dodgerblue', fontWeight: 'bold',
        textDecoration: 'underline',
      }
    };
    return (
      <>
        <p>You are about to delete your order with address: </p>
        <p><span style={style.address}>{drop_off_address}</span></p>
        <p>If you want to change something of the order, you can <strong>Edit</strong> it instead of <strong>Delete</strong> it.</p>
      </>
    );
  }

  deleteOrder() {
    axios.delete(`${orderEndpoint}&order_id=${this.state.orderToDelete.id}`)
      .then((res) => {
        // console.log('resp', res);
        this.setState({ ownOrders: this.state.ownOrders.filter(o => o.id !== this.state.orderToDelete.id) });
      })
      .catch(err => {
        // console.log('Error', err);
      });
  }


  ownOrdersSection() {
    // console.log('own orders', this.state.ownOrders);
    const driverCellHTML = (order) => {
      // console.log('driver', order.driver);
      const driverInfoCard = (driver) => (
        <UserInfoCard
          name={driver.name}
          wechat={driver.wechat}
          phone={driver.phone}
          email={driver.email}
          vehiclePlateNumber={driver.vehicle_plate_number}
          vehicleMakeAndModel={driver.vehicle_make_and_model}
          vehicleColor={driver.vehicle_color}
        />
      );
      if (order.approved === null) {
        return <strong style={{ color: 'red' }}>新生身份审核中</strong>;
      } else if (order.approved === "0") {
        return <strong style={{ color: 'red' }}>新生身份审核失败</strong>;
      } else {
        if (order.driver) {
          return driverInfoCard(order.driver);
        } else {
          return '等待司机接单';
        }
      }
    }
    const actionCellHTML = (order) => {
      const orderDetailCard = `
        <div class="card-content">
          <table>
            <tr>
              <th><i class="material-icons">add_location</i></th>
              <td>${order.drop_off_address}</td>
            </tr>
            <tr>
              <th><i class="material-icons icon icon-paper-plane"/></th>
              <td>${order.flight}</td>
            </tr>
            <tr>
              <th><i class="material-icons">access_time</i></th>
              <td>${moment(order.arrival_datetime).format(datetimeDisplayFormat)}</td>
            </tr>
            <tr>
              <th><i class="material-icons">flag</i></th>
              <td>航站楼 Terminal ${order.arrival_terminal}</td>
            </tr>
            <tr>
              <th><i class="material-icons">people</i></th>
              <td>随行人数 ${order.companion_count}</td>
            </tr>
            <tr>
              <th><i class="material-icons icon icon-suitcase-with-wheels"></i></th>
              <td>行李数量 ${order.luggage_count}</td>
            </tr>
            <tr>
              <th><i class="material-icons">message</i></th>
              <td>${order.note}</td>
            </tr>
          </table>
        </div>
      `;

      const editOrder = () => {
        // console.log('order', order);
        this.props.history.push({
          pathname: '/edit-order',
          state: {order},
        });
      };

      return (
        <>
          <i className="material-icons small blue-text tooltipped" data-position="left" data-tooltip={orderDetailCard}>info</i>
          <i className="material-icons small yellow-text text-darken-3" style={{cursor: 'pointer'}} onClick={editOrder}>edit</i>
          <a data-target="modal-delete-own-order-confirmation" className="modal-trigger material-icons small red-text text-lighten-1" style={{cursor: 'pointer'}} onClick={() => this.setState({orderToDelete: order})}>delete_forever</a>
        </>
      );
    };
    return (
      <div className="section row">
        <h5 className="center-align">我的订单状态</h5>
        <table className="striped responsive-table">
          <thead>
            <tr>
              <th>飞机到达时间</th>
              <th>目的地址</th>
              <th>接机司机</th>
              <th>订单操作</th>
            </tr>
          </thead>
          <tbody>
            {
              this.state.ownOrders.map(order => (
                <tr key={order.id}>
                  <td>{moment(order.arrival_datetime).format(datetimeDisplayFormat)}</td>
                  <td>{order.drop_off_address}</td>
                  <td>{driverCellHTML(order)}</td>
                  <td>{actionCellHTML(order)}</td>
                </tr>
              ))
            }
          </tbody>
        </table>
      </div>
    );
  }

  managedOrdersSection() {
    const arrivalTimeHTML = order => moment(order.arrival_datetime).format(datetimeDisplayFormat);

    const actionCellHTML = (order) => {
      const orderDetailCard = `
        <div class="card-content">
          <table>
            <tr>
              <th><i class="material-icons">add_location</i></th>
              <td>${order.drop_off_address}</td>
            </tr>
            <tr>
              <th><i class="material-icons icon icon-paper-plane"/></th>
              <td>${order.flight}</td>
            </tr>
            <tr>
              <th><i class="material-icons">access_time</i></th>
              <td>${arrivalTimeHTML(order)}</td>
            </tr>
            <tr>
              <th><i class="material-icons">flag</i></th>
              <td>航站楼 Terminal ${order.arrival_terminal}</td>
            </tr>
            <tr>
              <th><i class="material-icons">people</i></th>
              <td>随行人数 ${order.companion_count}</td>
            </tr>
            <tr>
              <th><i class="material-icons icon icon-suitcase-with-wheels"></i></th>
              <td>行李数量 ${order.luggage_count}</td>
            </tr>
            <tr>
              <th><i class="material-icons">message</i></th>
              <td>${order.note}</td>
            </tr>
          </table>
        </div>
      `;
      return (
        <>
          <a className="tooltipped" data-position="left" data-tooltip={orderDetailCard} style={{cursor: 'pointer'}}>订单详情</a>
          &nbsp;|&nbsp;
          <a data-target='modal-drop-confirmation' className="modal-trigger" onClick={() => this.setState({orderToDrop: order})} style={{cursor: 'pointer'}}>放弃</a>
        </>
      );
    };
    return (
      <div className="section row">
        <h5 className="center-align">我抢到的订单</h5>
        <table className="striped">
          <thead>
            <tr>
              <th>飞机到达时间</th>
              <th>目的地址</th>
              <th>联系方式</th>
              <th>订单操作</th>
            </tr>
          </thead>
          <tbody>
            {
              this.state.managedOrders.map((order) => (
                <tr key={order.passenger.email}>
                  <td>{arrivalTimeHTML(order)}</td>
                  <td>{order.drop_off_address}</td>
                  <td>
                    <UserInfoCard
                      name={order.passenger.name}
                      wechat={order.passenger.wechat}
                      email={order.passenger.email}
                      phone={order.passenger.phone}
                    />
                  </td>
                  <td>{actionCellHTML(order)}</td>
                </tr>
              ))
            }
          </tbody>
        </table>
      </div>
    );
  }

  pendingDriverMessage() {
    return (
      <div className="card-panel red lighten-3 white-text center-align">
        <p>司机身份审核中，请耐心等待</p>
        <p>我们会通过邮件通知您审核结果</p>
        <p>审核通过后就可以看到抢单选项了</p>
      </div>
    );
  }

  hasOrderWaitingForApprovalMessage() {
    return (
      <div className="card-panel red lighten-3 white-text center-align">
        <p>新生身份审核中，请耐心等待</p>
        <p>审核通过后订单才会提供给司机们接单</p>
        <p>我们会通过邮件通知您审核结果</p>
      </div>
    );
  }

  autoDismissMessage() {
    const refMessage = React.createRef();
    setTimeout(() => {refMessage.current.remove();}, 3000);
    return (<div ref={refMessage}>
      {this.props.location.state.autoDismissMessage}
    </div>);
  }

  actionsMenu() {
    const actionPickOrders = <li>
      <Link to="/pick-orders" className="btn-floating blue"><i className="material-icons">add_shopping_cart</i></Link>
      <a className="btn-floating mobile-fab-tip">抢单</a>
    </li>;

    const actionApplyDriver = <li>
      <Link to={{
        pathname: "/driver-application",
        state: {
          user: this.state.user
        }
      }}
      className="btn-floating green">
        <i className="material-icons">directions_car</i>
      </Link>
      <a className="btn-floating mobile-fab-tip">申请成为司机</a>
    </li>;

    const actionCreateOrder = <li>
      <Link to="/new-order" className="btn-floating red"><i className="large material-icons">add_circle</i></Link>
      <a className="btn-floating mobile-fab-tip">创建接机订单</a>
    </li>;

    return (
      <div className="fixed-action-btn" ref={this.refPageActionsFAB}>
        <a className="btn-floating btn-large red"><i className="large material-icons">add</i></a>
        <ul>
          { this.state.ownOrders.length == 0 && actionCreateOrder }
          { this.state.user.role == 'DRIVER' && actionPickOrders }
          { this.state.user.role == 'PASSENGER' && actionApplyDriver }
          <li>
            <Link to="/edit-user-info" className="btn-floating yellow darken-1"><i className="material-icons">edit</i></Link>
            <a className="btn-floating mobile-fab-tip">更新个人信息</a>
          </li>
          <li>
            <a className="btn-floating blue-grey" rel="nofollow" href="/pickup?auth=logout"><i className="material-icons">exit_to_app</i></a>
            <a className="btn-floating mobile-fab-tip">Log Out</a>
          </li>
        </ul>
      </div>
    );
  }


  /**
   * Modules:
   * - Shared Modules:
    * - 我的订单状态 - Hide if is driver only ( driver and no orders )
   *   - 等待司机接单
   *   - 显示司机头像和姓名，鼠标悬停显示具体联系方式
   * - Drivers Only:
   *  - 我承接的订单列表
   *  - 抢单
   * - Actions Menu:
   *   - 创建订单
   *   - 更改个人信息
   *   - 申请成为司机
   *
   */
  render() {

    return (
      <main className="container">
        { this.props.location.state && this.props.location.state.autoDismissMessage && this.autoDismissMessage() }
        { this.state.user.role == 'PENDING_DRIVER' && this.pendingDriverMessage() }
        { this.state.hasOrderWaitingForApproval && this.hasOrderWaitingForApprovalMessage() }
        { this.state.user.role == 'DRIVER' && this.managedOrdersSection() }
        { this.ownOrdersSection() }
        { this.actionsMenu() }
        <ConfirmationModal
          modalID="modal-drop-confirmation"
          body={this.confirmDropOrderModalText()}
          abortText="Keep"
          confirmText="Confirm Dropping"
          confirmAction={this.dropOrder}
        />
        <ConfirmationModal
          modalID="modal-delete-own-order-confirmation"
          body={this.confirmDeleteOrderModalText()}
          abortText="Keep"
          confirmText="Confirm Deletion"
          confirmAction={this.deleteOrder}
        />
      </main>
    );
  }
}
