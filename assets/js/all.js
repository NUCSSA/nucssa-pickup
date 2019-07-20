
document.addEventListener('DOMContentLoaded', () => {
  // init tabs
  M.Tabs.init(document.querySelectorAll('.tabs'));

  // registration form validation
  const reg_form = document.querySelector('form.register');
  if (reg_form) {
    reg_form.addEventListener('submit', (e) => {
      const form = e.target;
      const password1 = form.querySelector('input[name="user[password]"]').value;
      const password2 = form.querySelector('input[name="user[password2]"]').value;
      if (password1 !== password2) {
        form.querySelector('#message').innerHTML = `
        <div class="card-panel red lighten-3 white-text center-align">
        Password Doesn't Match!
        </div>`;
        e.preventDefault();
        return false;
      }
      return true;
    });
  }
});
