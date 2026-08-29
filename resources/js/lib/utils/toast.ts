import Swal from 'sweetalert2';

const corConfirmacaoApp = '#d97706';
const corCancelamentoApp = '#6b7280';

export const toastSucesso = (mensagem: string) => {
    Swal.fire({
        title: "Sucesso!",
        text: mensagem,
        icon: "success"
    });
}
export const toastErro = (mensagem: string) => {
    Swal.fire({
        title: "Erro!",
        text: mensagem,
        icon: "error"
    });
}

export const toastAviso = (mensagem: string) => {
    Swal.fire({
        title: "Atenção!",
        text: mensagem,
        icon: "warning"
    });
}

export const toastInfo = (mensagem: string) => {
    Swal.fire({
        title: "Informação",
        text: mensagem,
        icon: "info"
    });
}

export const toastConfirmacao = async (mensagem: string) => {

    const retorno = await Swal.fire({
        title: "Cuidado!",
        text: mensagem,
        icon: "warning",
        showCancelButton: true,
        reverseButtons: true,
        cancelButtonText: "Não",
        confirmButtonText: "Sim",
        confirmButtonColor: corConfirmacaoApp,
        cancelButtonColor: corCancelamentoApp,
    });

    return retorno.isConfirmed
}