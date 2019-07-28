<form method="post">
  <input type="hidden" name="order_id" value="<?php echo $order_id; ?>" />
  <input type="hidden" name="role" value="<?php echo $role; ?>" />
  <table>
    <tr>
      <th>请为您的司机打分: </th>
      <td>
        <div class="rate">
          <input type="radio" name="driver-rating" id="driver-star5" value="5">
          <label for="driver-star5" title="text">5 stars</label>

          <input type="radio" name="driver-rating" id="driver-star4" value="4">
          <label for="driver-star4" title="text">4 stars</label>

          <input type="radio" name="driver-rating" id="driver-star3" value="3">
          <label for="driver-star3" title="text">3 stars</label>

          <input type="radio" name="driver-rating" id="driver-star2" value="2">
          <label for="driver-star2" title="text">2 stars</label>

          <input type="radio" name="driver-rating" id="driver-star1" value="1">
          <label for="driver-star1" title="text">1 stars</label>
        </div>
      </td>
    </tr>
    <tr>
      <th>请为接机活动打分: </th>
      <td>
        <div class="rate">
          <input type="radio" name="activity-rating" id="activity-star5" value="5">
          <label for="activity-star5" title="text">5 stars</label>

          <input type="radio" name="activity-rating" id="activity-star4" value="4">
          <label for="activity-star4" title="text">4 stars</label>

          <input type="radio" name="activity-rating" id="activity-star3" value="3">
          <label for="activity-star3" title="text">3 stars</label>

          <input type="radio" name="activity-rating" id="activity-star2" value="2">
          <label for="activity-star2" title="text">2 stars</label>

          <input type="radio" name="activity-rating" id="activity-star1" value="1">
          <label for="activity-star1" title="text">1 stars</label>
        </div>
      </td>
    </tr>
    <tr>
      <th>您有什么想对我们说的</th>
      <td>
        <textarea class="materialize-textarea" name="comment" data-length="1000"></textarea>
      </td>
    </tr>
  </table>

  <div class="row center-align">
    <button type="submit" class="waves-effect waves-light btn">提交</button>
  </div>
</form>