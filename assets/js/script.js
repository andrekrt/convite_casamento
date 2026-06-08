const weddingDate = new Date('2026-08-01T16:00:00-03:00').getTime();
const counterElement = document.getElementById('contador');

function updateCounter() {
    const now = new Date().getTime();
    const distance = weddingDate - now;

    if (!counterElement) {
        return;
    }

    if (distance <= 0) {
        counterElement.innerHTML = 'Chegou o grande dia!';
        return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance / (1000 * 60 * 60)) % 24);
    const minutes = Math.floor((distance / (1000 * 60)) % 60);
    const seconds = Math.floor((distance / 1000) % 60);

    counterElement.innerHTML = `
        Faltam ${days} dias, ${hours}h ${minutes}min ${seconds}s
    `;
}

function verMensagemConvidado(button) {
  const nome = button.dataset.nome || "Convidado";
  const mensagem = button.dataset.mensagem || "";

  if (!mensagem.trim()) {
    return;
  }

  Swal.fire({
    title: "Mensagem de " + nome,
    text: mensagem,
    icon: "info",
    confirmButtonText: "Fechar",
    confirmButtonColor: "#7b2d35",
  });
}

updateCounter();
setInterval(updateCounter, 1000);