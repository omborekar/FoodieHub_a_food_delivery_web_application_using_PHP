<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:template match="/receipt">
    <html>
    <head>
      <title>Order Receipt</title>
      <style>
        body { font-family: Arial; padding: 20px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .summary { margin-top: 20px; text-align: right; }
        .summary p { margin: 5px 0; }
      </style>
    </head>
    <body>
      <h2>FoodieHub Order Receipt</h2>
      <p><strong>Order ID:</strong> <xsl:value-of select="order_id"/></p>
      <p><strong>User:</strong> <xsl:value-of select="user"/></p>
      <p><strong>Payment Method:</strong> <xsl:value-of select="payment_method"/></p>
      <p><strong>Order Date:</strong> <xsl:value-of select="order_date"/></p>

      <table>
        <tr>
          <th>Dish Name</th>
          <th>Quantity</th>
          <th>Unit Price</th>
          <th>Total Price</th>
        </tr>
        <xsl:for-each select="items/item">
          <tr>
            <td><xsl:value-of select="name"/></td>
            <td><xsl:value-of select="quantity"/></td>
            <td>₹<xsl:value-of select="unit_price"/></td>
            <td>₹<xsl:value-of select="total_price"/></td>
          </tr>
        </xsl:for-each>
      </table>

      <div class="summary">
        <p><strong>Subtotal:</strong> ₹<xsl:value-of select="subtotal"/></p>
        <p><strong>GST (12%):</strong> ₹<xsl:value-of select="gst"/></p>
        <p><strong>Delivery Charges:</strong> ₹<xsl:value-of select="delivery"/></p>
        <p><strong>Other Charges:</strong> ₹<xsl:value-of select="other"/></p>
        <p><strong>Total:</strong> ₹<xsl:value-of select="grand_total"/></p>
      </div>

      <p style="text-align:center; margin-top:30px;">Thank you for ordering from FoodieHub!</p>
    </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
