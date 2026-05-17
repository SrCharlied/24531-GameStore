import { describe, expect, it, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router-dom';

import LoginPage from '../pages/LoginPage';
import { AuthProvider } from '../context/AuthContext';

vi.mock('../api/client', () => {
  return {
    default: {
      get: vi.fn(() => Promise.reject({ response: { status: 401 } })),
      post: vi.fn(() => Promise.reject({
        response: { status: 401, data: { message: 'Usuario o contraseña incorrectos.' } },
      })),
    },
    ensureCsrfCookie: vi.fn(() => Promise.resolve()),
  };
});

function renderPage() {
  return render(
    <MemoryRouter>
      <AuthProvider>
        <LoginPage />
      </AuthProvider>
    </MemoryRouter>
  );
}

beforeEach(() => {
  vi.clearAllMocks();
});

describe('LoginPage', () => {
  it('renderiza los campos de usuario y contraseña', async () => {
    renderPage();
    expect(await screen.findByLabelText(/usuario/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/contraseña/i)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /entrar/i })).toBeInTheDocument();
  });

  it('muestra el mensaje del servidor cuando las credenciales son incorrectas', async () => {
    renderPage();
    const user = userEvent.setup();

    await user.type(await screen.findByLabelText(/usuario/i), 'admin');
    await user.type(screen.getByLabelText(/contraseña/i), 'wrong');
    await user.click(screen.getByRole('button', { name: /entrar/i }));

    await waitFor(() => {
      expect(screen.getByText(/usuario o contraseña incorrectos/i)).toBeInTheDocument();
    });
  });
});
