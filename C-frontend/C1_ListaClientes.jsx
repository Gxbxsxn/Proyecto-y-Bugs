import { useState, useEffect } from "react";
import axios from "axios";

/**
 * C1 · El listado no se actualiza (10 pts)
 *
 * CAUSA DEL BUG:
 * El useEffect tiene un array de dependencias vacío `[]`, lo que significa
 * que React solo ejecuta ese efecto UNA VEZ, cuando el componente se monta.
 * Aunque `filtro` cambie por props después, el efecto nunca se vuelve a
 * ejecutar, así que el fetch nunca se repite y la lista queda "congelada"
 * con el resultado del filtro inicial (o de filtro vacío).
 *
 * CORRECCIÓN: agregar `filtro` al array de dependencias, para que el
 * efecto se vuelva a disparar cada vez que cambie. De paso, agrego manejo
 * de "efecto obsoleto" (evitar condición de carrera si el usuario cambia
 * el filtro rápido y una respuesta vieja llega después que una nueva).
 */
function ListaClientes({ filtro }) {
  const [clientes, setClientes] = useState([]);

  useEffect(() => {
    let cancelado = false;

    axios.get(`/api/clientes?filtro=${filtro}`).then((res) => {
      // Evita pisar el resultado más reciente con una respuesta vieja
      // que llegó tarde (race condition típica al escribir rápido en
      // un buscador).
      if (!cancelado) {
        setClientes(res.data);
      }
    });

    return () => {
      cancelado = true;
    };
  }, [filtro]); // <- la dependencia que faltaba

  return (
    <ul>
      {clientes.map((c) => (
        <li key={c.id}>{c.nombre}</li>
      ))}
    </ul>
  );
}

export default ListaClientes;

/**
 * Nota para la entrevista: el `cancelado`/cleanup no era estrictamente
 * parte del bug pedido (que era solo el array de dependencias vacío),
 * pero lo agrego porque es una consecuencia directa de re-disparar el
 * efecto en cada cambio de filtro: sin esa protección, si el usuario
 * escribe rápido, una respuesta anterior más lenta podría sobrescribir
 * una más reciente y más rápida.
 */
