import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";

/**
 * C2 · Slice de Redux Toolkit (10 pts)
 *
 * Thunk asíncrono que consulta GET /pagos?cliente_id=... y un slice que
 * maneja los tres estados: carga (pending), éxito (fulfilled) y error
 * (rejected).
 */

export const fetchPagos = createAsyncThunk(
  "pagos/fetchPagos",
  async (clienteId, { rejectWithValue }) => {
    try {
      const res = await axios.get("/pagos", {
        params: { cliente_id: clienteId },
      });
      return res.data;
    } catch (err) {
      // rejectWithValue permite controlar exactamente qué llega a
      // action.payload en el caso 'rejected', en vez de depender del
      // objeto de error crudo de axios.
      return rejectWithValue(
        err.response?.data?.message ?? "Error al obtener los pagos"
      );
    }
  }
);

const pagosSlice = createSlice({
  name: "pagos",
  initialState: {
    items: [],
    status: "idle", // 'idle' | 'loading' | 'succeeded' | 'failed'
    error: null,
  },
  reducers: {
    // Reducers síncronos adicionales podrían ir aquí si se necesitan
    // (ej. limpiar el estado al cambiar de cliente).
    limpiarPagos(state) {
      state.items = [];
      state.status = "idle";
      state.error = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchPagos.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchPagos.fulfilled, (state, action) => {
        state.status = "succeeded";
        state.items = action.payload;
      })
      .addCase(fetchPagos.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload ?? action.error.message;
      });
  },
});

export const { limpiarPagos } = pagosSlice.actions;

// Selectores básicos, útiles para no repetir `state.pagos.xxx` en cada
// componente.
export const selectPagos = (state) => state.pagos.items;
export const selectPagosStatus = (state) => state.pagos.status;
export const selectPagosError = (state) => state.pagos.error;

export default pagosSlice.reducer;

/**
 * Uso típico en un componente:
 *
 * const dispatch = useDispatch();
 * const pagos = useSelector(selectPagos);
 * const status = useSelector(selectPagosStatus);
 *
 * useEffect(() => {
 *   dispatch(fetchPagos(clienteId));
 * }, [dispatch, clienteId]);
 */
