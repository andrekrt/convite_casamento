function copiarLink(button) {
  const input = button.parentElement.querySelector("input");

  if (!input) {
    return;
  }

  input.select();
  input.setSelectionRange(0, 99999);

  const texto = input.value;

  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(texto).then(() => {
      mostrarFeedbackCopiado(button);
    });

    return;
  }

  document.execCommand("copy");
  mostrarFeedbackCopiado(button);
}

function mostrarFeedbackCopiado(button) {
  const originalHtml = button.innerHTML;
  const originalTitle = button.title;

  button.innerHTML = '<i class="fa-solid fa-check"></i>';
  button.title = "Copiado!";

  setTimeout(() => {
    button.innerHTML = originalHtml;
    button.title = originalTitle || "Copiar link";
  }, 1800);
}

function configurarConfirmacaoFormulario(selector, config) {
  document.querySelectorAll(selector).forEach((form) => {
    form.addEventListener("submit", function (event) {
      event.preventDefault();

      const nome = form.dataset.nome || "este convite";

      if (typeof Swal === "undefined") {
        const confirmado = confirm(config.text.replace("{nome}", nome));

        if (confirmado) {
          form.submit();
        }

        return;
      }

      Swal.fire({
        title: config.title,
        text: config.text.replace("{nome}", nome),
        icon: config.icon,
        showCancelButton: true,
        confirmButtonText: config.confirmButtonText,
        cancelButtonText: "Cancelar",
        confirmButtonColor: config.confirmButtonColor,
        cancelButtonColor: "#6c757d",
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });
}

configurarConfirmacaoFormulario(".form-enviar", {
  title: "Enviar convite?",
  text: 'Deseja enviar o convite para "{nome}" agora?',
  icon: "question",
  confirmButtonText: "Sim, enviar",
  confirmButtonColor: "#36558f",
});

configurarConfirmacaoFormulario(".form-resetar", {
  title: "Resetar resposta?",
  text: 'Deseja resetar a resposta de "{nome}"? O convite voltará para pendente.',
  icon: "warning",
  confirmButtonText: "Sim, resetar",
  confirmButtonColor: "#c58b22",
});

configurarConfirmacaoFormulario(".form-excluir", {
  title: "Excluir convite?",
  text: 'Tem certeza que deseja excluir "{nome}"? Essa ação não poderá ser desfeita.',
  icon: "error",
  confirmButtonText: "Sim, excluir",
  confirmButtonColor: "#9f1f1f",
});

if (
  typeof flashMessage !== "undefined" &&
  flashMessage &&
  typeof Swal !== "undefined"
) {
  Swal.fire({
    title: flashMessage.titulo,
    text: flashMessage.mensagem,
    icon: flashMessage.tipo,
    confirmButtonColor: "#7b2d35",
  });
}
