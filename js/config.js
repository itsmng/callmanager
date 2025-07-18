const { h, render } = preact;
const { useState } = preactHooks;
const html = htm.bind(h);

function App() {
  return html`
    <div>
      <h1>Call Manager</h1>
    </div>
  `;
}

document.addEventListener('DOMContentLoaded', () => {
  render(html`<${App} />`, document.getElementById('plugin_callmanager_config_form'));
});