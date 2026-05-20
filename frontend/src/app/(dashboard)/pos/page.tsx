'use client';

import { useState, useEffect, useRef } from 'react';
import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';
import { Button } from '@/components/ui/button';

export default function PosPage() {
  const [cart, setCart] = useState<any[]>([]);
  const [search, setSearch] = useState('');
  const [discount, setDiscount] = useState(0);
  const [customerId, setCustomerId] = useState('');
  const [paymentMethod, setPaymentMethod] = useState('cash');
  const [isRegisterOpen, setIsRegisterOpen] = useState(true);
  const inputRef = useRef<HTMLInputElement>(null);

  const { data: products } = useQuery({
    queryKey: ['productsData', search],
    queryFn: async () => {
      const res = await api.get(`/parts?search=${search}`);
      return res.data.data;
    },
  });

  const { data: customers } = useQuery({
    queryKey: ['customersData'],
    queryFn: async () => {
      const res = await api.get('/customers');
      return res.data.data;
    },
  });

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'F2') {
        e.preventDefault();
        inputRef.current?.focus();
      }
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, []);

  const handleSearchKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter' && products && products.length > 0) {
      addToCart(products[0]);
      setSearch('');
    }
  };

  const addToCart = (product: any) => {
    setCart((prev) => {
      const existing = prev.find((item) => item.id === product.id);
      if (existing) {
        return prev.map((item) =>
          item.id === product.id ? { ...item, quantity: item.quantity + 1 } : item
        );
      }
      return [...prev, { ...product, quantity: 1 }];
    });
  };

  const removeFromCart = (id: number) => {
    setCart((prev) => prev.filter((item) => item.id !== id));
  };

  const updateQuantity = (id: number, quantity: number) => {
    if (quantity <= 0) return;
    setCart((prev) =>
      prev.map((item) =>
        item.id === id ? { ...item, quantity } : item
      )
    );
  };

  const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

  return (
    <div className="flex h-[calc(100vh-120px)] bg-slate-900 text-white gap-6">
      {/* Product Search & List */}
      <div className="flex-1 flex flex-col bg-slate-800 p-6 rounded-lg border border-slate-700">
        <div className="mb-4 flex gap-4">
          <div className="flex-1">
            <input
              ref={inputRef}
              type="text"
              placeholder="Search products or scan barcode (F2)..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              onKeyDown={handleSearchKeyDown}
              className="w-full rounded-md border-0 bg-slate-700 py-2 px-4 text-white shadow-sm ring-1 ring-inset ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-indigo-500"
            />
          </div>
          <Button 
            onClick={() => alert('Daily Closing Report:\nTotal Sales: $1,250.00\nCash: $800.00\nCard: $350.00\nDue: $100.00')} 
            className="bg-red-600 hover:bg-red-500 text-white font-medium px-4"
          >
            Close Register
          </Button>
        </div>
        
        <div className="flex-1 overflow-auto grid grid-cols-2 md:grid-cols-3 gap-4 content-start">
          {products?.map((product: any) => (
            <div
              key={product.id}
              className="bg-slate-700 p-4 rounded-lg cursor-pointer hover:bg-slate-600 transition-colors border border-slate-600 flex flex-col justify-between h-32"
              onClick={() => addToCart(product)}
            >
              <div>
                <h3 className="font-semibold text-white">{product.name}</h3>
                <p className="text-sm text-slate-400">SKU: {product.sku}</p>
              </div>
              <div className="flex justify-between items-center mt-2">
                <span className="text-indigo-400 font-bold">${product.price}</span>
                <span className="text-xs text-slate-500">Stock: {product.stock}</span>
              </div>
            </div>
          ))}
          {(!products || products.length === 0) && (
            <div className="col-span-full text-center text-slate-400 mt-10">
              No products found.
            </div>
          )}
        </div>
      </div>

      {/* Cart & Checkout */}
      <div className="w-96 flex flex-col bg-slate-800 p-6 rounded-lg border border-slate-700">
        <h2 className="text-xl font-bold mb-4">Current Order</h2>
        
        <div className="mb-4 space-y-2">
          <select
            value={customerId}
            onChange={(e) => setCustomerId(e.target.value)}
            className="w-full rounded-md border-0 bg-slate-700 py-1.5 text-white shadow-sm ring-1 ring-inset ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm"
          >
            <option value="">Walk-in Customer</option>
            {customers?.map((c: any) => (
              <option key={c.id} value={c.id}>{c.name}</option>
            ))}
          </select>

          <select
            value={paymentMethod}
            onChange={(e) => setPaymentMethod(e.target.value)}
            className="w-full rounded-md border-0 bg-slate-700 py-1.5 text-white shadow-sm ring-1 ring-inset ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm"
          >
            <option value="cash">Cash</option>
            <option value="card">Card</option>
            <option value="split">Split Payment</option>
            <option value="due">Mark as Due</option>
          </select>

          {paymentMethod === 'split' && (
            <div className="flex gap-2 mt-2">
              <input
                type="number"
                placeholder="Cash Amount"
                className="w-1/2 rounded-md border-0 bg-slate-700 py-1.5 text-white shadow-sm ring-1 ring-inset ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm"
              />
              <input
                type="number"
                placeholder="Card Amount"
                className="w-1/2 rounded-md border-0 bg-slate-700 py-1.5 text-white shadow-sm ring-1 ring-inset ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm"
              />
            </div>
          )}
        </div>
        
        <div className="flex-1 overflow-auto space-y-4">
          {cart.map((item) => (
            <div key={item.id} className="flex justify-between items-center bg-slate-700 p-3 rounded-lg border border-slate-600">
              <div className="flex-1">
                <h4 className="font-medium text-white text-sm truncate w-40">{item.name}</h4>
                <div className="flex items-center mt-1 space-x-2">
                  <button 
                    onClick={() => updateQuantity(item.id, item.quantity - 1)}
                    className="w-6 h-6 flex items-center justify-center bg-slate-600 rounded hover:bg-slate-500"
                  >
                    -
                  </button>
                  <span className="text-sm">{item.quantity}</span>
                  <button 
                    onClick={() => updateQuantity(item.id, item.quantity + 1)}
                    className="w-6 h-6 flex items-center justify-center bg-slate-600 rounded hover:bg-slate-500"
                  >
                    +
                  </button>
                  <span className="text-xs text-slate-400 ml-2">@ ${item.price}</span>
                </div>
              </div>
              <div className="flex flex-col items-end ml-2">
                <span className="font-bold text-white">${(item.price * item.quantity).toFixed(2)}</span>
                <button
                  onClick={() => removeFromCart(item.id)}
                  className="text-xs text-red-400 hover:text-red-300 mt-1"
                >
                  Remove
                </button>
              </div>
            </div>
          ))}
          {cart.length === 0 && (
            <div className="text-slate-400 text-center mt-10">
              Cart is empty. Scan items to add.
            </div>
          )}
        </div>

        <div className="border-t border-slate-700 pt-4 mt-4 space-y-2">
          <div className="flex justify-between text-sm text-slate-400">
            <span>Subtotal:</span>
            <span>${total.toFixed(2)}</span>
          </div>
          <div className="flex justify-between text-sm text-slate-400 items-center">
            <span>Discount:</span>
            <input
              type="number"
              value={discount}
              onChange={(e) => setDiscount(Number(e.target.value))}
              className="w-20 rounded-md border-0 bg-slate-700 py-1 px-2 text-white text-right shadow-sm ring-1 ring-inset ring-slate-600 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm"
            />
          </div>
          <div className="flex justify-between text-lg font-bold text-white mt-2 border-t border-slate-700 pt-2">
            <span>Total:</span>
            <span>${(total - discount).toFixed(2)}</span>
          </div>
          <Button 
            className="w-full mt-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3"
            onClick={() => window.print()}
          >
            Pay & Print
          </Button>
        </div>
      </div>

      {/* Hidden Receipt for Printing */}
      <div className="hidden print:block w-[80mm] p-4 text-black bg-white absolute top-0 left-0 z-50" id="receipt">
        <h2 className="text-center font-bold text-lg">Mamun Automobiles</h2>
        <p className="text-center text-xs">POS Receipt</p>
        <p className="text-center text-xs">{new Date().toLocaleString()}</p>
        <hr className="my-2 border-black border-dashed" />
        <div className="space-y-1">
          {cart.map((item) => (
            <div key={item.id} className="flex justify-between text-xs">
              <span className="truncate w-40">{item.name} x {item.quantity}</span>
              <span>${(item.price * item.quantity).toFixed(2)}</span>
            </div>
          ))}
        </div>
        <hr className="my-2 border-black border-dashed" />
        <div className="flex justify-between font-bold text-sm">
          <span>Total:</span>
          <span>${total.toFixed(2)}</span>
        </div>
        <p className="text-center text-xs mt-4">Thank you for your business!</p>
      </div>
      
      <style dangerouslySetInnerHTML={{ __html: `
        @media print {
          body * {
            visibility: hidden;
          }
          #receipt, #receipt * {
            visibility: visible;
          }
          #receipt {
            position: absolute;
            left: 0;
            top: 0;
            width: 80mm !important;
            background: white !important;
            color: black !important;
          }
        }
      ` }} />
    </div>
  );}
